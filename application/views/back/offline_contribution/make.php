<div id="content-container">
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header text-overflow">Make Offline Contribution</h1>
        </div>
        <ol class="breadcrumb">
            <li><a href="#"><i class="demo-pli-home"></i></a></li>
            <li><a href="#">Offline Contribution</a></li>
            <li class="active">Make Contribution</li>
        </ol>
    </div>

    <style>
        /* Custom Button Styles from Bulk Payment */
        .custom-btn {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-size: 1.4rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            text-decoration: none !important;
            display: inline-block;
        }
        .custom-btn:hover {
            color: white;
            background: linear-gradient(135deg, #2563eb, #1e40af);
            transform: translateY(-1px);
            box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.2);
        }
        .btn-green-gradient {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        .btn-green-gradient:hover {
            background: linear-gradient(135deg, #059669, #047857);
        }
        .custom-badge {
            background: rgba(255, 255, 255, 0.3);
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            margin-left: 8px;
            font-weight: bold;
            font-size: 1.2rem;
            color: white;
        }
    </style>

    <div id="page-content">
        <!-- Top Action Buttons -->
        <div class="row">
            <div class="col-sm-12">
                <div class="text-right" style="margin-bottom: 20px;">
                     <a href="<?= base_url('offline_contribution/offline_invoice_list') ?>" class="custom-btn" style="margin-right: 10px;">
                        <i class="fa fa-file-text"></i> View Invoices
                    </a>
                    <a href="<?= base_url('offline_contribution/offline_contribution_cart') ?>" class="custom-btn btn-green-gradient">
                        <i class="fa fa-shopping-cart"></i> Contribution Cart 
                        <span class="custom-badge" id="cart_count_badge">0</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Select Members for Contribution</h3>
            </div>

            <!-- Selection Controls -->
            <div class="pad-all bord-btm">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="btn-group">
                            <button type="button" class="btn btn-default" id="select-all-btn">
                                <i class="fa fa-check-square-o"></i> Select All
                            </button>
                            <button type="button" class="btn btn-default" id="deselect-all-btn">
                                <i class="fa fa-square-o"></i> Deselect All
                            </button>
                        </div>
                        <span class="text-muted" style="margin-left: 10px;" id="selection-text">No members selected</span>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button type="button" class="btn btn-primary" id="add_selected_btn" disabled>
                            <i class="fa fa-plus"></i> Add Selected to Cart
                        </button>
                    </div>
                </div>
            </div>

            <div class="panel-body">
                <?php if (!empty($members)): ?>
                    <div class="table-responsive">
                        <table id="member_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">
                                        <input type="checkbox" id="select-all-checkbox">
                                    </th>
                                    <th>#</th>
                                    <th>Member ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Area</th>
                                    <th>Legion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($members as $row): ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox"
                                                   class="member-checkbox"
                                                   value="<?= $row['member_id'] ?>"
                                                   data-member-id="<?= $row['member_id'] ?>">
                                        </td>
                                        <td><?= $i++ ?></td>
                                        <td><?= $row['member_profile_id'] ?></td>
                                        <td><strong><?= $row['first_name'] . ' ' . $row['last_name'] ?></strong></td>
                                        <td><?= $row['email'] ?></td>
                                        <td><?= $row['mobile'] ?></td>
                                        <td><?= $row['area_name'] ?? 'N/A' ?></td>
                                        <td><?= $row['legion_name'] ?? 'N/A' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> No members found available for contribution selection.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    // Note: DataTables is removed to prevent script errors if the library is not loaded globally.
    // If pagination is needed, please ensure the DataTables plugin is included in the project.

    // Load initial cart count
    loadCartCount();

    // Select All Checkbox (Header)
    $('#select-all-checkbox').on('change', function () {
        let isChecked = this.checked;
        $('.member-checkbox').prop('checked', isChecked);
        updateUI();
    });

    // Individual Checkbox Change
    $('#member_table').on('change', '.member-checkbox', function () {
        updateUI();
        
        // Update header checkbox
        let total = $('.member-checkbox').length;
        let checked = $('.member-checkbox:checked').length;
        $('#select-all-checkbox').prop('checked', (total > 0 && total === checked));
    });

    // Select All Button
    $('#select-all-btn').on('click', function() {
        $('.member-checkbox').prop('checked', true);
        $('#select-all-checkbox').prop('checked', true);
        updateUI();
    });

    // Deselect All Button
    $('#deselect-all-btn').on('click', function() {
        $('.member-checkbox').prop('checked', false);
        $('#select-all-checkbox').prop('checked', false);
        updateUI();
    });

    // Add to Cart Logic
    $('#add_selected_btn').on('click', function () {
        // Collect selected IDs directly from DOM
        let selectedIds = [];
        $('.member-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert('Please select at least one member.');
            return;
        }

        let btn = $(this);
        let originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Adding...');

        $.ajax({
            url: '<?= base_url("offline_contribution/offline_add_to_cart") ?>',
            type: 'POST',
            data: { 
                member_ids: selectedIds
            },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    // Update cart count
                    $('#cart_count_badge').text(res.count);
                    
                    // Show success
                    if (typeof $.niftyNoty == 'function') {
                         $.niftyNoty({
                            type: 'success',
                            container: 'floating',
                            html: res.message,
                            timer: 3000
                        });
                    } else {
                        alert(res.message);
                    }

                    // Reset selection
                    $('.member-checkbox').prop('checked', false);
                    $('#select-all-checkbox').prop('checked', false);
                    updateUI();
                } else {
                    alert(res.message || 'Failed to add members');
                }
            },
            error: function () {
                alert('Server error occurred.');
            },
            complete: function () {
                btn.prop('disabled', false).html(originalText);
                updateUI();
            }
        });
    });

    function updateUI() {
        let count = $('.member-checkbox:checked').length;
        if (count > 0) {
            $('#selection-text').text(count + ' member(s) selected');
            $('#add_selected_btn').prop('disabled', false);
        } else {
            $('#selection-text').text('No members selected');
            $('#add_selected_btn').prop('disabled', true);
        }
    }

    // Call updateUI on load in case of browser caching checkboxes
    updateUI();

    function loadCartCount() {
        $.ajax({
            url: '<?= base_url("offline_contribution/offline_get_cart_count") ?>',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#cart_count_badge').text(res.count || 0);
                }
            }
        });
    }
});
</script>
