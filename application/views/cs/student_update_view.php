<?php include 'nav.php'; ?>

<div class="container container-gap">
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="panel panel-default">
                <table class="table table-striped table-bordered table-hover table-responsive">
                    <thead>
                    <tr>
                        <th>FAN</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Error</th>
                        <th>Action</th>
                    </tr>
                    </thead>

                    <?php foreach ($items as $item) { ?>
                        <tr>
                            <td><?php echo $item['fan']; ?></td>
                            <td><?php echo $item['name']; ?></td>
                            <td><?php echo $item['status']; ?></td>
                            <td><?php echo $item['last_created']; ?></td>
                            <td><?php echo $item['error_message']; ?></td>
                            <td><button class="btn btn-primary btn-xs" data-title="update"><span class="glyphicon glyphicon-ok-circle"></span></button></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>


