<div style="width:100%;">
    <h2>Rollback</h2>

    <div class="row button-form">
        <div class="span-12">
            <div class="row">
                <ul class="flat">
                    <li>
                        <a href="<?php echo URL::site('/migrate/show').'?token='.$_GET['token'] ?>" class="button">Show</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <ul>
    <?php foreach($logs as $log): ?>
    <li>
        <pre style="margin:0;padding:0;"><code><?php echo $log ?></code></pre>
    </li>
    <?php endforeach ?>
    </ul>
</div>