<div id="content-container">
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header text-overflow">Award Report</h1>
        </div>
        <ol class="breadcrumb">
            <li><a href="#"><?= translate('home')?></a></li>
            <li><a href="#">Awards</a></li>
            <li class="active">Report</li>
        </ol>
    </div>
    <div id="page-content">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-list-alt"></i> Award Entry Report</h3>
            </div>
            <div class="panel-body">
                <?php if (isset($is_super_admin) && $is_super_admin): ?>
                    <form method="post" action="<?= base_url('admin/award/delete_bulk'); ?>" onsubmit="return confirm('Are you sure you want to delete selected entries?');">
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-bottom: 10px;">Delete Selected</button>
                <?php endif; ?>
                <table class="table table-striped table-bordered dataTable" id="award_report_table">
                    <thead>
                        <tr>
                            <th>
                                <?php if (isset($is_super_admin) && $is_super_admin): ?>
                                    <input type="checkbox" id="check_all">
                                <?php else: ?>
                                    #
                                <?php endif; ?>
                            </th>
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
                            <td>
                                <?php if (isset($is_super_admin) && $is_super_admin): ?>
                                    <input type="checkbox" name="entries[]" value="<?= $entry['id']; ?>" class="check_item">
                                <?php else: ?>
                                    <?= $i++; ?>
                                <?php endif; ?>
                            </td>
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
                                <?php if (isset($can_delete) && $can_delete): ?>
                                    <a href="<?= base_url('admin/award/delete/' . $entry['id']); ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this award entry?');">
                                        <i class="fa fa-trash"></i> Delete
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (isset($is_super_admin) && $is_super_admin): ?>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#award_report_table').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            "columnDefs": [
                { "orderable": false, "targets": 0 } // Disable sorting on first column (checkbox)
            ]
        });

        // Check All functionality
        $('#check_all').on('click', function() {
            var isChecked = $(this).prop('checked');
            $('.check_item').prop('checked', isChecked);
        });
    });
</script>
