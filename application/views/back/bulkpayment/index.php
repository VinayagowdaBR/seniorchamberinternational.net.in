<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Selection</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        #content-container {
            max-width: 1500px;
            margin: 0 auto;
            padding: 2rem 3rem;
            margin-top: 80px;
            padding-top: 1rem;
        }

        #page-head {
            margin-bottom: 2rem;
        }

        #page-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .page-header {
            font-size: 2.25rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        /* Button Styles */
        button {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-size: 1.60rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        button:hover {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            transform: translateY(-1px);
            box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.2);
        }

        button:active {
            transform: translateY(0);
        }

        button:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-info {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }

        .btn-default {
            background: linear-gradient(135deg, #64748b, #475569);
        }

        .btn-default:hover {
            background: linear-gradient(135deg, #475569, #334155);
        }

        /* Cart Button */
        .proceed-cart-btn {
            background: linear-gradient(135deg, #10b981, #059669);
            padding: 0.75rem 1.5rem;
            font-size: 1.60rem;
        }

        .proceed-cart-btn:hover {
            background: linear-gradient(135deg, #059669, #047857);
        }

        .cart-count {
            background: rgba(255, 255, 255, 0.3);
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            margin-left: 8px;
            font-weight: bold;
        }

        /* Panel Styles */
        .panel {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .selection-controls {
            background: #f8f9fa;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-group {
            display: flex;
            gap: 10px;
        }

        .selection-info {
            font-size: 1.30rem;
            color: #64748b;
            font-weight: 500;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            font-size: 1.95rem;
            min-width: 1000px;
        }

        thead {
            background: linear-gradient(135deg, #475569, #334155);
        }

        th {
            padding: 1.25rem 1rem;
            text-align: left;
            font-weight: 600;
            color: white;
            font-size: 1.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: background-color 0.2s ease;
        }

        tbody tr:hover {
            background-color: #f8fafc;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        td {
            padding: 1.25rem 1rem;
            vertical-align: middle;
        }

        .member-checkbox, #select-all-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #3b82f6;
        }

        .member-name {
            font-weight: 600;
            color: #1e293b;
        }

        /* Badge Styles */
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 1.30rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-gold {
            background: linear-gradient(135deg, #f7b731 0%, #f79c1c 100%);
            color: white;
        }

        .badge-silver {
            background: linear-gradient(135deg, #95afc0 0%, #535c68 100%);
            color: white;
        }

        .badge-free {
            background: #e2e8f0;
            color: #64748b;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 1.30rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        /* Gender Badge Styles */
        .gender-badge {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 1.30rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .gender-badge.male {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }

        .gender-badge.female {
            background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
            color: #be185d;
        }

        .gender-badge i {
            font-size: 1.125rem;
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: #64748b;
            font-size: 1.125rem;
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            #content-container {
                padding: 1rem;
                margin-top: 70px;
                padding-top: 0.5rem;
            }

            .page-header {
                font-size: 1.875rem;
            }

            .selection-controls {
                flex-direction: column;
                gap: 1rem;
            }

            .btn-group {
                width: 100%;
                justify-content: center;
            }

            .selection-info {
                width: 100%;
                text-align: center;
            }

            table {
                font-size: 1.75rem;
            }

            th, td {
                padding: 1rem 0.5rem;
            }
        }

        @media (max-width: 480px) {
            #content-container {
                margin-top: 60px;
            }

            button {
                font-size: 1.40rem;
                padding: 0.6rem 1.2rem;
            }

            .proceed-cart-btn {
                width: 100%;
            }
        }

        /* Loading Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        tbody tr {
            animation: fadeIn 0.3s ease forwards;
        }

        tbody tr:nth-child(even) {
            animation-delay: 0.1s;
        }

        tbody tr:nth-child(odd) {
            animation-delay: 0.05s;
        }
    </style>
</head>
<body>

<div id="content-container">
    
    <!-- Page Header -->
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header">Member Selection</h1>
        </div>
    </div>
    
    <!-- Cart Button -->
    <div style="text-align: right; margin-bottom: 1.5rem;">
        <button type="button" class="proceed-cart-btn" id="proceed-to-cart-btn" disabled>
            <i class="fa fa-shopping-cart"></i> Proceed To Cart 
            <span class="cart-count" id="selected-count">0</span>
        </button>
    </div>
    
    <!-- Member Selection Panel -->
    <div class="panel">
        
        <!-- Selection Controls -->
        <div class="selection-controls">
            <div class="btn-group">
                <button type="button" class="btn btn-info" id="select-all-btn">
                    <i class="fa fa-check-square-o"></i> Select All
                </button>
                <button type="button" class="btn btn-default" id="deselect-all-btn">
                    <i class="fa fa-square-o"></i> Deselect All
                </button>
            </div>
            <div class="selection-info">
                <span id="selection-text">No members selected</span>
            </div>
        </div>

        <!-- Member Table -->
        <?php if (empty($all_members)): ?>
            <div class="no-data">
                <i class="fa fa-info-circle"></i>
                <h4>No Members Found</h4>
                <p>There are no members available at the moment.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table id="member-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">
                                <input type="checkbox" id="select-all-checkbox">
                            </th>
                            <th>Member Name</th>
                            <th>Gender</th>
                            <th>Age</th>
                            <th>Membership</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_members as $member): 
                            // Convert gender: 1 = Male, 2 = Female
                            $genderDisplay = 'N/A';
                            if ($member->gender == 1 || $member->gender == '1') {
                                $genderDisplay = 'Male';
                            } elseif ($member->gender == 2 || $member->gender == '2') {
                                $genderDisplay = 'Female';
                            }
                        ?>
                        <tr>
                            <td style="text-align: center;">
                                <input type="checkbox" class="member-checkbox" 
                                       data-id="<?php echo $member->member_id; ?>"
                                       data-member-id="<?php echo $member->member_profile_id; ?>"
                                       data-member-name="<?php echo $member->first_name . ' ' . $member->last_name; ?>"
                                       data-gender="<?php echo $genderDisplay; ?>"
                                       data-age="<?php echo isset($member->age) ? $member->age : 'N/A'; ?>">
                            </td>
                            <td>
                                <span class="member-name"><?php echo $member->first_name . ' ' . $member->last_name; ?></span>
                            </td>
                            <td>
                                <?php if ($genderDisplay == 'Male'): ?>
                                    <span class="gender-badge male">
                                        <i class="fa fa-male"></i> Male
                                    </span>
                                <?php elseif ($genderDisplay == 'Female'): ?>
                                    <span class="gender-badge female">
                                        <i class="fa fa-female"></i> Female
                                    </span>
                                <?php else: ?>
                                    <span><?php echo $genderDisplay; ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo isset($member->age) ? $member->age : 'N/A'; ?></td>
                            <td>
                                <?php if ($member->membership == 1): ?>
                                    <span class="badge badge-silver">Silver</span>
                                <?php elseif ($member->membership == 2): ?>
                                    <span class="badge badge-gold">Gold</span>
                                <?php else: ?>
                                    <span class="badge badge-free">Free</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($member->status == 'ok' || strtolower($member->status) == 'approved'): ?>
                                    <span class="status-badge status-approved">Approved</span>
                                <?php else: ?>
                                    <span class="status-badge status-pending"><?php echo ucfirst($member->status); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<!-- JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<script>
$(document).ready(function() {
    
    // Select All Checkbox (in header)
    $('#select-all-checkbox').change(function() {
        $('.member-checkbox').prop('checked', $(this).prop('checked'));
        updateSelectedCount();
    });

    // Select All Button
    $('#select-all-btn').click(function() {
        $('.member-checkbox').prop('checked', true);
        $('#select-all-checkbox').prop('checked', true);
        updateSelectedCount();
    });

    // Deselect All Button
    $('#deselect-all-btn').click(function() {
        $('.member-checkbox').prop('checked', false);
        $('#select-all-checkbox').prop('checked', false);
        updateSelectedCount();
    });

    // Individual checkbox change
    $('.member-checkbox').change(function() {
        updateSelectedCount();
        
        // Update header checkbox state
        if ($('.member-checkbox:checked').length === $('.member-checkbox').length) {
            $('#select-all-checkbox').prop('checked', true);
        } else {
            $('#select-all-checkbox').prop('checked', false);
        }
    });

    // Update selected count and button state
    function updateSelectedCount() {
        var count = $('.member-checkbox:checked').length;
        var total = $('.member-checkbox').length;
        
        $('#selected-count').text(count);
        
        if (count > 0) {
            $('#proceed-to-cart-btn').prop('disabled', false);
            $('#selection-text').text(count + ' of ' + total + ' members selected');
        } else {
            $('#proceed-to-cart-btn').prop('disabled', true);
            $('#selection-text').text('No members selected');
        }
    }

    // Proceed to Cart
    $('#proceed-to-cart-btn').click(function() {
        var selectedItems = [];
        
        $('.member-checkbox:checked').each(function() {
            selectedItems.push({
                id: $(this).data('id'),
                member_id: $(this).data('member-id'),
                member_name: $(this).data('member-name'),
                gender: $(this).data('gender'),
                age: $(this).data('age')
            });
        });

        if (selectedItems.length === 0) {
            alert('Please select at least one member');
            return;
        }

        // Store in session via AJAX
        $.ajax({
            url: '<?= base_url() ?>admin/bulkpayment/store_cart_items',
            type: 'POST',
            data: { items: JSON.stringify(selectedItems) },
            beforeSend: function() {
                $('#proceed-to-cart-btn').prop('disabled', true)
                    .html('<i class="fa fa-spinner fa-spin"></i> Processing...');
            },
            success: function(response) {
                var res = JSON.parse(response);
                if (res.status === 'success') {
                    window.location.href = res.redirect;
                } else {
                    alert(res.message);
                    updateProceedButton();
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                updateProceedButton();
            }
        });
    });
    
    function updateProceedButton() {
        var count = $('.member-checkbox:checked').length;
        $('#proceed-to-cart-btn').prop('disabled', false)
            .html('<i class="fa fa-shopping-cart"></i> Proceed To Cart <span class="cart-count">' + count + '</span>');
    }
    
    // Initialize count on page load
    updateSelectedCount();

});
</script>
</body>
</html>
