<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-list-alt"></i> Award Entry Report</h3>
    </div>
    <div class="panel-body">
        <table class="table table-striped table-bordered dataTable" id="award_report_table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Award Name</th>
                    <th>Year</th>
                    <th>Applicant</th>
                    <th>Status</th>
                    <th>Points</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($entries as $entry): ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td>
                        <b><?= $entry['category']; ?></b><br>
                        <small>(<?= ucfirst($entry['award_for']); ?>)</small>
                    </td>
                    <td><?= $entry['year']; ?></td>
                    <td>
                        <?php if ($entry['award_for'] == 'legion'): ?>
                            Legion: <b><?= $entry['legion_name']; ?></b>
                        <?php else: ?>
                            Nominee: <b><?= $entry['nominee_name']; ?></b><br>
                            Legion: <?= $entry['legion_name']; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                            $label = 'warning';
                            if($entry['status'] == 'approved') $label = 'success';
                            if($entry['status'] == 'rejected') $label = 'danger';
                        ?>
                        <span class="label label-<?= $label; ?>"><?= ucfirst($entry['status']); ?></span>
                    </td>
                    <td><?= (int)$entry['total_points']; ?></td>
                    <td><?= date('d-M-Y', strtotime($entry['created_at'])); ?></td>
                    <td>
                        <a href="<?= base_url('admin/award/view_details/' . $entry['id']); ?>" class="btn btn-info btn-xs" target="_blank">
                            <i class="fa fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#award_report_table').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });
    });
</script>
