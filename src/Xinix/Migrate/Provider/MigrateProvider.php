<?php

namespace Xinix\Migrate\Provider;

use ROH\Util\Inflector;

class MigrateProvider extends \Bono\Provider\Provider
{
    public function initialize()
    {
        $app = $this->app;
        $migrate = $this;

        $this->options = array_merge(array(
            'baseDir' => '../migrations',
        ), $this->options ?: array());

        if ($app->config('bono.cli') !== true) {
            if (empty($this->options['token'])) {
                return;
            }

            if ($app->request->get('token') !== $this->options['token']) {
                return;
            }

            $d = explode(DIRECTORY_SEPARATOR.'src', __DIR__);
            $app->theme->addBaseDirectory($d[0]);
        }

        $app->get('/migrate', function () use ($app) {
            $app->redirect('/migrate/show?token='.$app->request->get('token'));
        });

        $app->get('/migrate/show', function () use ($app, $migrate) {
            $entries = $migrate->find();

            if ($app->config('bono.cli')) {
                foreach ($entries as $entry) {
                    $l = sprintf(
                        "| %-45s | %s | %s |\n",
                        substr($entry['title'], 0, 45),
                        $entry['time']->format('Y-m-d H:i:s'),
                        $entry['status']
                    );
                    echo $l;
                }
            } else {
                $app->response->set('entries', $entries);
                $app->response->template('_migrate/show');
            }

        });

        $app->get('/migrate/rollback', function () use ($app, $migrate) {
            $lockFile = $this->getLockFile();

            $entries = $migrate->find();

            $logs = array();

            foreach ($entries as $k => $entry) {
                if ($entry['status'] == 'Y') {
                    include($entry['path']);
                    $Clazz = $entry['class'];
                    $entries[$k]['object'] = new $Clazz;
                    $lastEntry = $entries[$k];
                    $beforeEntry = @$entries[$k+1];
                    break;
                }
            }

            if (isset($lastEntry)) {
                $logs[] = $this->downgrade($lastEntry);

                if ($beforeEntry) {
                    file_put_contents($lockFile, $beforeEntry['stime']);
                } else {
                    file_put_contents($lockFile, '');
                }
                $logs[] = "Success migrate...";
            } else {
                $logs[] = "Nothing to migrate";
            }

            if ($app->config('bono.cli')) {
                foreach ($logs as $log) {
                    echo trim($log)."\n";
                }
            } else {
                $app->response->set('logs', $logs);
                $app->response->template('_migrate/rollback');
            }
        });

        $app->get('/migrate/run', function () use ($app, $migrate) {
            $lockFile = $this->getLockFile();

            $entries = array_reverse($migrate->find());

            $logs = array();

            $mRules = array();
            try {
                // first foreach will include migrate rules
                foreach ($entries as $k => $entry) {
                    if ($entry['status'] == 'N') {
                        include($entry['path']);
                        $Clazz = $entry['class'];
                        $entries[$k]['object'] = new $Clazz;
                    }
                }

                $lastEntry = null;

                // second foreach will run
                foreach ($entries as $entry) {
                    if ($entry['status'] == 'N') {
                        $mRules[] = array($lastEntry, $entry);

                        $logs[] = $this->upgrade($entry);

                        $lastEntry = $entry;
                    }
                }

                if (isset($lastEntry)) {
                    file_put_contents($lockFile, $lastEntry['stime']);
                    $logs[] = "Success migrate...";
                } else {
                    $logs[] = "Nothing to migrate";
                }
            } catch(\Exception $e) {
                $logs[] = "Error caught, rollbacking...";
                $mRules = array_reverse($mRules);

                $entry = $mRules[0][1];
                $logs[] = $this->downgrade($entry);

                if (isset($mRules[0][0])) {
                    file_put_contents($lockFile, $mRules[0][0]['stime']);
                }
            }

            if ($app->config('bono.cli')) {
                foreach ($logs as $log) {
                    echo trim($log)."\n";
                }
            } else {
                $app->response->set('logs', $logs);
                $app->response->template('_migrate/run');
            }
        });
    }

    public function upgrade($entry) {
        $log = 'Upgrading '.$entry['class']."...\n";
        ob_start();
        $entry['object']->up();
        $log .= trim(ob_get_clean());
        return trim($log);
    }

    public function downgrade($entry) {
        $log = 'Downgrading '.$entry['class']."...\n";
        ob_start();
        $entry['object']->down();
        $log .= trim(ob_get_clean());
        return trim($log);
    }

    public function getBaseDir() {

        return rtrim(getcwd().'/'.$this->options['baseDir'], '/');
    }

    public function getLockFile() {
        return $this->getBaseDir().'/migrate.lock';
    }

    public function find() {
        $entries = array();

        $baseDir = $this->getBaseDir();
        $lockFile = $this->getLockFile();

        $lock = '';
        if (is_readable($lockFile)) {
            $lock = trim(file_get_contents($lockFile));
            if ($lock) {
                $lock = \DateTime::createFromFormat('YmdHis', $lock);
            }
        }


        if (is_dir($baseDir)) {
            if ($dh = opendir($baseDir)) {
                while (($file = readdir($dh)) !== false) {
                    $path = $baseDir .'/'. $file;
                    $pinfo = pathinfo($path);
                    if (filetype($path) === 'file' && strtolower($pinfo['extension']) === 'php') {
                        $f = explode('_', $pinfo['filename']);
                        $st = $f[1];
                        $t = \DateTime::createFromFormat('YmdHis', $f[1]);
                        $f = Inflector::humanize($f[0]);
                        $c = $pinfo['filename'];

                        $s = 'N';
                        if (isset($lock)) {
                            if ($t <= $lock) {
                                $s = 'Y';
                            }
                        }


                        $entries[] = array(
                            'class' => $c,
                            'title' => $f,
                            'time' => $t,
                            'stime' => $st,
                            'status' => $s,
                            'path' => $path,
                        );

                    }
                }
                closedir($dh);
            }
        }

        usort($entries, function($a, $b) {
            return $a['time'] < $b['time'];
        });

        return $entries;
    }
}
