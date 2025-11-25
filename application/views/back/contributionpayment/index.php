<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<style>
    #content-container {
        max-width: 1600px;
        margin: 0 auto;
        padding: 2rem 3rem;
        padding-top: 1rem;
        margin-top: 90px !important;
        background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
        min-height: calc(100vh - 90px);
    }

    @media (max-width: 768px) {
        #content-container { 
            padding: 1rem; 
            margin-top: 80px !important; 
        }
    }

    .page-header { 
        font-size: 2.5rem; 
        font-weight: 800; 
        background: linear-gradient(135deg, #1e293b, #475569);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0 0 2rem 0; 
        padding-bottom: 1.5rem; 
        border-bottom: 3px solid #3b82f6;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .page-header::before {
        content: "👥";
        font-size: 2rem;
        -webkit-text-fill-color: initial;
    }

    .selection-controls { 
        background: linear-gradient(135deg, #ffffff, #f8fafc);
        padding: 1.5rem 2rem; 
        margin-bottom: 2rem; 
        display: flex; 
        flex-wrap: wrap; 
        gap: 1.5rem; 
        justify-content: space-between; 
        align-items: center; 
        border-radius: 16px; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border: 1px solid rgba(59, 130, 246, 0.1);
    }

    .btn-group { 
        display: flex; 
        gap: 12px; 
    }

    .btn-group button, .btn-proceed { 
        padding: 0.875rem 1.75rem; 
        border: none; 
        border-radius: 10px; 
        font-size: 1rem; 
        font-weight: 700; 
        cursor: pointer; 
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-select-all { 
        background: linear-gradient(135deg, #3b82f6, #2563eb); 
        color: white; 
    }

    .btn-select-all:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
    }

    .btn-deselect-all { 
        background: linear-gradient(135deg, #6b7280, #4b5563); 
        color: white; 
    }

    .btn-deselect-all:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(107, 114, 128, 0.4);
    }

    .btn-proceed { 
        background: linear-gradient(135deg, #10b981, #059669); 
        color: white; 
        display: flex; 
        align-items: center; 
        gap: 10px;
        position: relative;
        overflow: hidden;
    }

    .btn-proceed:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    }

    .btn-proceed:disabled {
        background: linear-gradient(135deg, #9ca3af, #6b7280);
        cursor: not-allowed;
        opacity: 0.6;
    }

    .btn-proceed::before {
        content: "🛒";
        font-size: 1.2rem;
    }

    .cart-badge { 
        background: rgba(255, 255, 255, 0.3); 
        padding: 6px 14px; 
        border-radius: 20px; 
        font-size: 0.875rem; 
        font-weight: 700;
        min-width: 30px;
        text-align: center;
    }

    .selection-info { 
        color: #475569; 
        font-weight: 600;
        font-size: 1.1rem;
        background: linear-gradient(135deg, #e0e7ff, #dbeafe);
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        border: 2px solid #3b82f6;
    }

    .selection-info::before {
        content: "✓ ";
        color: #10b981;
        font-weight: bold;
    }

    .member-table-wrapper { 
        background: #fff; 
        border-radius: 16px; 
        overflow: hidden; 
        box-shadow: 0 20px 50px rgba(0,0,0,0.12);
        border: 1px solid #e2e8f0;
    }

    .member-table { 
        width: 100%; 
        border-collapse: collapse; 
        font-size: 1rem; 
    }

    .member-table thead { 
        background: linear-gradient(135deg, #1e293b, #334155); 
        color: white; 
    }

    .member-table thead th {
        padding: 1.25rem 1rem;
        text-align: left;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.875rem;
        border-bottom: 3px solid #3b82f6;
    }

    .member-table tbody tr {
        border-bottom: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .member-table tbody tr:hover {
        background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        transform: scale(1.01);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    }

    .member-table tbody td {
        padding: 1rem;
        vertical-align: middle;
    }

    .member-checkbox { 
        width: 20px; 
        height: 20px; 
        cursor: pointer; 
        accent-color: #3b82f6;
        border: 2px solid #cbd5e1;
        border-radius: 4px;
    }

    .gender-badge, .membership-badge, .status-badge {
        display: inline-block;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .gender-badge.male {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
    }

    .gender-badge.female {
        background: linear-gradient(135deg, #ec4899, #db2777);
        color: white;
    }

    .membership-badge.free {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        color: white;
    }

    .membership-badge.gold {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        color: white;
    }

    .membership-badge.silver {
        background: linear-gradient(135deg, #d1d5db, #9ca3af);
        color: #1f2937;
    }

    .status-badge.approved {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }

    .status-badge.pending {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
    }

    .loading-overlay {
        position: fixed;
        top: 0; 
        left: 0;
        width: 100%; 
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        align-items: center; 
        justify-content: center;
        z-index: 9999;
        backdrop-filter: blur(5px);
    }

    .spinner-container {
        text-align: center;
    }

    .spinner { 
        border: 5px solid rgba(255, 255, 255, 0.3); 
        border-top: 5px solid #3b82f6; 
        border-radius: 50%; 
        width: 60px; 
        height: 60px; 
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }

    .loading-text {
        color: white;
        font-size: 1.2rem;
        font-weight: 600;
    }

    @keyframes spin { 
        0% { transform: rotate(0deg); } 
        100% { transform: rotate(360deg); } 
    }

    .alert-container { 
        position: fixed; 
        top: 110px; 
        right: 30px; 
        z-index: 9998; 
        max-width: 400px; 
    }

    .alert { 
        padding: 18px 24px; 
        margin-bottom: 15px; 
        border-radius: 12px; 
        color: white;
        font-weight: 600;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        animation: slideIn 0.3s ease;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert::before {
        font-size: 1.5rem;
    }

    .alert-success { 
        background: linear-gradient(135deg, #10b981, #059669); 
    }

    .alert-success::before {
        content: "✓";
    }

    .alert-error { 
        background: linear-gradient(135deg, #ef4444, #dc2626); 
    }

    .alert-error::before {
        content: "⚠";
    }

    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @media (max-width: 1024px) {
        .selection-controls {
            flex-direction: column;
            align-items: stretch;
        }

        .btn-group {
            width: 100%;
            justify-content: space-between;
        }

        .btn-group button {
            flex: 1;
        }

        .selection-info {
            text-align: center;
        }

        .btn-proceed {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 768px) {
        .page-header {
            font-size: 1.75rem;
        }

        .member-table {
            font-size: 0.875rem;
        }

        .member-table thead th,
        .member-table tbody td {
            padding: 0.75rem 0.5rem;
        }

        .alert-container {
            right: 10px;
            left: 10px;
            max-width: none;
        }
    }
</style>

<div id="content-container">
    <h1 class="page-header">Member Selection for Contribution Payment</h1>

    <div class="selection-controls">
        <div class="btn-group">
            <button class="btn-select-all" id="select-all-btn">Select All</button>
            <button class="btn-deselect-all" id="deselect-all-btn">Deselect All</button>
        </div>
        <div class="selection-info">
            <span id="selection-count">No members selected</span>
        </div>
        <button class="btn-proceed" id="proceed-to-cart-btn" disabled>
            Proceed To Cart <span class="cart-badge" id="cart-count">0</span>
        </button>
    </div>

    <div class="member-table-wrapper">
        <table class="member-table" id="member-table">
            <thead>
                <tr>
                    <th width="5%">
                        <input type="checkbox" id="select-all-checkbox" class="member-checkbox">
                    </th>
                    <th>MEMBER NAME</th>
                    <th>GENDER</th>
                    <th>AGE</th>
                    <th>MEMBERSHIP</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_members as $member): ?>
                <tr>
                    <td>
                        <input type="checkbox" 
                               class="member-checkbox member-select" 
                               data-member-id="<?= $member->member_id; ?>"
                               value="<?= $member->member_id; ?>">
                    </td>

                    <td><strong><?= htmlspecialchars($member->first_name.' '.$member->last_name); ?></strong></td>

                    <td>
                        <?php if ($member->gender == 1): ?>
                            <span class="gender-badge male">Male</span>
                        <?php else: ?>
                            <span class="gender-badge female">Female</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php 
                            if (!empty($member->dob)) {
                                try {
                                    echo (new DateTime())->diff(new DateTime($member->dob))->y;
                                } catch(Exception $e) { echo 'N/A'; }
                            } else echo 'N/A';
                        ?>
                    </td>

                    <td>
                        <?php 
                            $m = $member->membership ?? 1;
                            $map = [1=>'free',2=>'gold',3=>'silver'];
                            $class = $map[$m] ?? 'free';
                        ?>
                        <span class="membership-badge <?= $class ?>"><?= ucfirst($class) ?></span>
                    </td>

                    <td>
                        <span class="status-badge <?= $member->status=='approved'?'approved':'pending' ?>">
                            <?= ucfirst($member->status ?? 'pending') ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="loading-overlay" id="loading-overlay">
    <div class="spinner-container">
        <div class="spinner"></div>
        <div class="loading-text">Processing your request...</div>
    </div>
</div>

<div class="alert-container" id="alert-container"></div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {

    // Initialize DataTable
    var table = $('#member-table').DataTable({
        pageLength: 25,
        order: [[1, "asc"]],
        columnDefs: [
            { orderable: false, targets: 0 }
        ],
        language: { 
            search: "Search members:" 
        }
    });

    // Helper Functions
    function showAlert(msg, type) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-error';
        $('#alert-container').html('<div class="alert ' + alertClass + '">' + msg + '</div>');
        setTimeout(function() {
            $('#alert-container').fadeOut(300, function() { 
                $(this).html("").show(); 
            });
        }, 3500);
    }

    function toggleLoading(show) { 
        if(show) {
            $('#loading-overlay').css('display', 'flex').hide().fadeIn(200);
        } else {
            $('#loading-overlay').fadeOut(200);
        }
    }

    // CRITICAL: Update Selection Count - Works across ALL pages
    function updateSelectionCount() {
        // Count checked checkboxes across ALL pages using DataTables API
        var count = table.rows().nodes().to$().find('.member-select:checked').length;
        
        $('#cart-count').text(count);

        if (count === 0) {
            $('#selection-count').text("No members selected");
            $('#proceed-to-cart-btn').prop('disabled', true);
        } else {
            $('#selection-count').text(count + " member" + (count > 1 ? "s" : "") + " selected");
            $('#proceed-to-cart-btn').prop('disabled', false);
        }
    }

    // Select All Button - Works across ALL pages
    $('#select-all-btn').click(function() {
        table.rows().nodes().to$().find('.member-select').prop('checked', true);
        $('#select-all-checkbox').prop('checked', true);
        updateSelectionCount();
    });

    // Deselect All Button
    $('#deselect-all-btn').click(function() {
        table.rows().nodes().to$().find('.member-select').prop('checked', false);
        $('#select-all-checkbox').prop('checked', false);
        updateSelectionCount();
    });

    // Header Checkbox - Only current page
    $('#select-all-checkbox').on('change', function() {
        var checked = this.checked;
        var rows = table.rows({ page: 'current' }).nodes();
        $(rows).find('.member-select').prop('checked', checked);
        updateSelectionCount();
    });

    // Individual Checkbox Change
    $('#member-table tbody').on('change', '.member-select', function() {
        updateSelectionCount();
    });

    // PROCEED TO CART - Fixed Implementation
    $('#proceed-to-cart-btn').click(function(e) {
        e.preventDefault();

        var selectedIds = [];
        
        // Collect all checked member IDs from ALL pages
        table.rows().nodes().to$().find('.member-select:checked').each(function() {
            var memberId = $(this).attr('data-member-id') || $(this).val();
            if (memberId) {
                selectedIds.push(memberId);
            }
        });

        console.log("Selected Member IDs:", selectedIds);

        if (selectedIds.length === 0) {
            showAlert("Please select at least one member", "error");
            return;
        }

        var $btn = $(this);
        var oldText = $btn.html();

        $btn.prop('disabled', true).html('🛒 <span class="cart-badge">⏳</span> Processing...');
        toggleLoading(true);

        // AJAX Call - Proper data structure
        $.ajax({
            url: '<?= base_url("admin/contributionpayment/store_cart_items"); ?>',
            type: 'POST',
            data: {
                member_ids: selectedIds,
                '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                toggleLoading(false);
                
                console.log("Server Response:", response);
                
                if (response.status === "success" || response.success === true) {
                    showAlert("✓ " + selectedIds.length + " members added to cart!", "success");
                    setTimeout(function() {
                        window.location.href = response.redirect || '<?= base_url("admin/contributionpayment/payment_cart"); ?>';
                    }, 1000);
                } else {
                    showAlert(response.message || "Error saving cart items", "error");
                    $btn.prop('disabled', false).html(oldText);
                }
            },
            error: function(xhr, status, error) {
                toggleLoading(false);
                console.error("AJAX Error:", xhr.responseText);
                showAlert("Server Error: " + error, "error");
                $btn.prop('disabled', false).html(oldText);
            }
        });
    });

    // Initial count update
    updateSelectionCount();
});
</script>
