<div style="width:100%;">
    <h2>Migration</h2>

    <div class="row button-form">
        <div class="span-12">
            <div class="row">
                <ul class="flat">
                    <li>
                        <a href="<?php echo URL::site('/migrate/run').'?token='.$_GET['token'] ?>" class="button">Run</a>
                    </li>
                    <li>
                        <a href="<?php echo URL::site('/migrate/rollback').'?token='.$_GET['token'] ?>" class="button">Rollback</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>


    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($entries as $entry): ?>
            <tr>
                <td><?php echo $entry['title'] ?></td>
                <td><?php echo $entry['time']->format('Y-m-d H:i:s') ?></td>
                <td><?php echo $entry['status'] ?></td>
            </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>