<?php
$story_data = $this->db->select('*')->from('happy_story')->where('approval_status', '1')->get()->result();
?>

<!-- CONTENT CONTAINER -->
<!--===================================================-->
<div id="content-container" class="container-fluid">
    <div id="page-head">
        <!-- Page Title -->
        <div id="page-title">
            <h1 class="page-header text-overflow"><?php echo translate('Projects')?></h1>
        </div>
        <!-- Breadcrumb -->
        <ol class="breadcrumb">
            <li><a href="<?= base_url() ?>admin"><?php echo translate('home')?></a></li>
            <li class="active"><?php echo translate('Projects')?></li>
        </ol>
    </div>

    <!-- Page Content -->
    <div id="page-content">
        <!-- Alerts -->
        <?php if (!empty($success_alert)): ?>
            <div class="alert alert-success alert-dismissible fade show" id="success_alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <?= $success_alert ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($danger_alert)): ?>
            <div class="alert alert-danger alert-dismissible fade show" id="danger_alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <?= $danger_alert ?>
            </div>
        <?php endif; ?>

        <!-- Basic Data Tables -->
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo translate('Projects_list')?></h3>
            </div>

            <?php
            $allowed_roles = [1, 2, 7, 8, 9];
            $current_user_role_id = $this->session->userdata('role_id');
            ?>

            <?php if (in_array($current_user_role_id, $allowed_roles)): ?>
                <div class="text-right mb-3">
                    <a href="<?= base_url() ?>admin/stories/add_story" class="btn btn-primary add-tooltip" data-toggle="tooltip" title="<?php echo translate('add_new_project')?>">
                        <i class="fa fa-plus"></i> <?php echo translate('add_new_project')?>
                    </a>
                </div>
            <?php else: ?>
                <div class="text-right mb-3">
                    <p class="text-muted"><?php echo translate('no_permission_to_add_project')?></p>
                </div>
            <?php endif; ?>

            <div class="panel-body">
                <!-- Date Range Form -->
                <form method="post" action="<?= base_url('admin/generate') ?>" id="date-range-form">
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <label for="report_year" class="form-label"><?php echo translate('select_year')?></label>
                            <select id="report_year" class="form-control">
                                <!-- Populated by JS -->
                            </select>
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <label for="date_range" class="form-label"><?php echo translate('select_date_range')?></label>
                            <select id="date_range" name="date_range" class="form-control" required disabled>
                                <option value=""><?php echo translate('choose_year_first')?></option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-success" id="generate_pdf" style="display: none;">
                                <i class="fa fa-file-pdf-o"></i> <?php echo translate('generate_pdf')?>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- DataTable -->
                <table id="stories_table" class="table table-striped table-bordered" style="width: 100%;">
                    <thead>
                        <tr>
                            <th><?php echo translate('image')?></th>
                            <th><?php echo translate('created_by')?></th>
                            <th><?php echo translate('title')?></th>
                            <th><?php echo translate('date')?></th>
                            <th><?php echo translate('description')?></th>
                            <th><?php echo translate('options')?></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approval_modal" tabindex="-1" aria-labelledby="approval_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approval_modal_label"><?php echo translate('confirm_your_action')?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p><?php echo translate('are_you_sure_you_want_to')?> "<b id="type_name"></b>" <?php echo translate('this_project')?>?</p>
                <input type="hidden" id="story_id" name="story_id" value="">
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo translate('close')?></button>
                    <button class="btn btn-primary" id="approval_status"><?php echo translate('confirm')?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles -->
<style>
.alert-dismissible {
    position: relative;
    padding-right: 4rem;
}
.alert-dismissible .close {
    position: absolute;
    top: 0;
    right: 0;
    padding: 0.75rem 1.25rem;
    color: inherit;
}
.panel-body {
    padding: 20px;
}
.text-right.mb-3 {
    margin-bottom: 1.5rem;
}
@media (max-width: 768px) {
    #stories_table th, #stories_table td {
        font-size: 14px;
    }
    .text-right.mb-3 {
        text-align: center;
    }
}
</style>

<script>
$(document).ready(function () {
    // Initialize DataTable
    let table = $('#stories_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?php echo base_url('admin/stories/list_data') ?>",
            dataType: "json",
            type: "POST",
            data: { '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>' }
        },
        columns: [
            { data: "image", orderable: false },
            { data: "partner_name" },
            { data: "title" },
            { data: "date" },
            { data: "description" },
            { data: "options", orderable: false }
        ],
        responsive: true,
        drawCallback: function () {
            $('.add-tooltip').tooltip();
        }
    });

    // Fade out alerts
    setTimeout(function () {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Populate Years and Date Ranges
    const startYear = 2024;
    const currentYear = new Date().getFullYear();
    const endYear = 2027; // As requested, explicit range until 2027 or higher
    const yearSelect = $('#report_year');
    const dateRangeSelect = $('#date_range');

    for (let y = startYear; y <= endYear; y++) {
        yearSelect.append(new Option(y, y));
    }
    // Set default to current year if within range, else first available
    if (currentYear >= startYear && currentYear <= endYear) {
        yearSelect.val(currentYear);
    } else {
        yearSelect.val(startYear);
    }
    
    function updateDateRanges() {
        const selectedYear = parseInt(yearSelect.val());
        dateRangeSelect.empty();
        
        if (!selectedYear) {
            dateRangeSelect.append(new Option('<?php echo translate("choose_year_first")?>', ''));
            dateRangeSelect.prop('disabled', true);
            return;
        }

        dateRangeSelect.prop('disabled', false);
        dateRangeSelect.append(new Option('<?php echo translate("choose_range")?>', ''));

        const nextYear = selectedYear + 1;
        const ranges = [
            { start: `${selectedYear}-04-01`, end: `${selectedYear}-06-10` },
            { start: `${selectedYear}-06-11`, end: `${selectedYear}-08-10` },
            { start: `${selectedYear}-08-11`, end: `${selectedYear}-10-10` },
            { start: `${selectedYear}-10-11`, end: `${selectedYear}-12-10` },
            { start: `${selectedYear}-12-11`, end: `${nextYear}-02-20` }
        ];

        ranges.forEach(range => {
            // Format date as "1 Apr - 10 Jun"
            const startDate = new Date(range.start);
            const endDate = new Date(range.end);
            const options = { day: 'numeric', month: 'short' };
            // Note: Date parsing from YYYY-MM-DD string is UTC in JS usually, but here for display labels mostly fine. 
            // To be safe and avoid timezone shifts showing wrong day, we can parse manually or append time.
            // Simple string manipulation for label is safer for consistency.
            
            const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            const startLabel = `${startDate.getDate()} ${months[startDate.getMonth()]} ${startDate.getFullYear()}`;
            const endLabel = `${endDate.getDate()} ${months[endDate.getMonth()]} ${endDate.getFullYear()}`;
            
            const label = `${startLabel} - ${endLabel}`;
            const value = `${range.start}|${range.end}`;
            dateRangeSelect.append(new Option(label, value));
        });
    }

    // Initialize logic
    updateDateRanges();

    // Year change listener
    yearSelect.on('change', function() {
        updateDateRanges();
        $('#generate_pdf').hide(); // Hide button until range selected
    });

    // Date range dropdown
    $('#date_range').on('change', function () {
        $('#generate_pdf').toggle(this.value !== '');
    });

    // PDF generation
    $('#generate_pdf').on('click', function (e) {
        e.preventDefault();
        const selectedRange = $('#date_range').val();
        if (selectedRange) {
            $('#generate_pdf').prop('disabled', true).text('<?php echo translate('generating')?>...');
            fetch("<?= base_url('admin/generate') ?>?range=" + encodeURIComponent(selectedRange))
                .then(response => {
                    if (!response.ok) throw new Error("Failed to generate PDF");
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement("a");
                    a.href = url;
                    a.download = `Report_${new Date().toISOString().replace(/[-:T]/g, '').slice(0, 15)}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    $('#generate_pdf').prop('disabled', false).text('<?php echo translate('generate_pdf')?>');
                })
                .catch(error => {
                    console.error("PDF generation error:", error);
                    alert("<?php echo translate('failed_to_generate_pdf')?>");
                    $('#generate_pdf').prop('disabled', false).text('<?php echo translate('generate_pdf')?>');
                });
        }
    });

    // Approval modal
    window.approval = function (status, story_id) {
        $('#approval_status').val(status);
        // Fix: If status is 1 (Approve), show 'approve'. If 0 (Unpublish), show 'unpublish'.
        $('#type_name').html(status == 1 ? "<?php echo translate('approve')?>" : "<?php echo translate('unpublish')?>");
        $('#story_id').val(story_id);
        $('#approval_modal').modal('show');
    };

    $('#approval_status').on('click', function () {
        const status = $(this).val();
        const story_id = $('#story_id').val();
        $.ajax({
            url: `<?= base_url() ?>admin/stories/approval/${status}/${story_id}`,
            success: function () {
                $('#approval_modal').modal('hide');
                table.ajax.reload(null, false); // Refresh table without resetting pagination
                
                // Show success alert
                const alertHtml = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <?php echo translate('approval_status_updated_successfully')?>
                    </div>`;
                
                // Remove existing alerts to prevent stacking
                $('.alert').remove();
                
                // Prepend to page content
                $('#page-content').prepend(alertHtml);
                
                // Auto fade out
                setTimeout(function () {
                    $('.alert').fadeOut('slow');
                }, 5000);
            },
            error: function (xhr) {
                alert("<?php echo translate('error_updating_status')?>: " + xhr.statusText);
            }
        });
    });

    // Direct delete functionality
    window.delete_story = function (id) {
        $.ajax({
            url: `<?= base_url() ?>admin/stories/delete/${id}`,
            success: function () {
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert("<?php echo translate('error_deleting_data')?>: " + xhr.statusText);
            }
        });
    };
});
</script>