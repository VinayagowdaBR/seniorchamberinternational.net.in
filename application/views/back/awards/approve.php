<?php
function get_entry_criteria($award_criteria, $entry) {
    $type = $entry['award_for'];
    $cat  = $entry['category'];
    return isset($award_criteria[$type][$cat]) ? $award_criteria[$type][$cat] : array();
}
?>

<div id="content-container">
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header text-overflow">Approve Awards</h1>
        </div>
        <ol class="breadcrumb">
            <li><a href="#"><?= translate('home')?></a></li>
            <li><a href="#">Awards</a></li>
            <li class="active">Approve</li>
        </ol>
    </div>
    <div id="page-content">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-check-circle"></i> Approve Award Entries</h3>
            </div>
        
            <div class="panel-body">
        
                <?php if (empty($entries)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fa fa-info-circle"></i> No award entries found.
                    </div>
                <?php endif; ?>
        
                <?php $idx = 1; foreach ($entries as $entry): 
                    $criteria     = get_entry_criteria($award_criteria, $entry);
                    $saved_points = json_decode($entry['points_json'], true) ?: array();
                ?>
                    <div class="panel panel-default" style="margin-bottom: 20px;">
                        <!-- Entry Header -->
                        <div class="panel-heading" style="background-color: #f5f5f5;">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4 style="margin: 0;">
                                        <strong>#<?= $idx++; ?> - <?= $entry['category']; ?></strong>
                                        <small class="text-muted">(<?= ucfirst($entry['award_for']); ?>)</small>
                                    </h4>
                                    <div style="margin-top: 8px;">
                                        <span class="label label-default">Year: <?= $entry['year']; ?></span>
                                        <?php if ($entry['award_for'] == 'legion'): ?>
                                            <span class="label label-info">Legion: <?= $entry['legion_name']; ?></span>
                                        <?php else: ?>
                                            <span class="label label-info">Nominee: <?= $entry['nominee_name']; ?></span>
                                        <?php endif; ?>
                                        <span class="label label-<?= $entry['status']=='approved'?'success':($entry['status']=='rejected'?'danger':'warning'); ?>">
                                            <?= ucfirst($entry['status']); ?>
                                        </span>
                                        <?php if ($entry['status'] == 'approved'): ?>
                                            <span class="label label-primary">Total Points: <strong><?= (int)$entry['total_points']; ?></strong></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-4 text-right">
                                    <a href="<?= base_url('admin/award/view_details/' . $entry['id']); ?>" 
                                       class="btn btn-info btn-sm" 
                                       target="_blank"
                                       style="margin-bottom: 5px;">
                                        <i class="fa fa-eye"></i> View Full Details
                                    </a>
                                    <br>
                                    <button type="button" 
                                            class="btn btn-primary btn-sm" 
                                            data-toggle="collapse" 
                                            data-target="#entry-<?= $entry['id']; ?>"
                                            onclick="toggleJudgeBtn(<?= $entry['id']; ?>)">
                                        <i class="fa fa-gavel"></i> <span id="judge-btn-<?= $entry['id']; ?>">Judge & Give Marks</span>
                                    </button>
                                </div>
                            </div>
                        </div>
        
                        <!-- Judging Panel -->
                        <div id="entry-<?= $entry['id']; ?>" class="panel-collapse collapse">
                            <div class="panel-body" style="background-color: #fafafa;">
                                
                                <!-- Entry Summary -->
                                <div class="row" style="margin-bottom: 20px;">
                                    <div class="col-md-12">
                                        <div class="well well-sm">
                                            <h5 style="margin-top: 0;"><i class="fa fa-info-circle"></i> Entry Summary</h5>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>Category:</strong> <?= $entry['category']; ?>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>Type:</strong> <?= ucfirst($entry['award_for']); ?>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>Year:</strong> <?= $entry['year']; ?>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>Submitted:</strong> <?= date('d-M-Y', strtotime($entry['created_at'])); ?>
                                                </div>
                                            </div>
                                            <?php if ($entry['award_for'] == 'legion'): ?>
                                                <div class="row" style="margin-top: 10px;">
                                                    <div class="col-md-6">
                                                        <strong>Legion:</strong> <?= $entry['legion_name']; ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong>Area:</strong> <?= $entry['area_id']; ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="row" style="margin-top: 10px;">
                                                    <div class="col-md-4">
                                                        <strong>Nominee:</strong> <?= $entry['nominee_name']; ?>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <strong>Legion:</strong> <?= $entry['legion_name']; ?>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <strong>Member ID:</strong> <?= $entry['member_id']; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <form class="form-horizontal" method="post" action="<?= base_url('admin/award/update_status/'.$entry['id']); ?>" id="judge-form-<?= $entry['id']; ?>">
        
                                    <?php if (!empty($criteria)): ?>
                                        <div class="panel panel-primary">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">
                                                    <i class="fa fa-list-check"></i> Judging Criteria & Marks
                                                </h4>
                                            </div>
                                            <div class="panel-body">
                                                <?php 
                                                $total_max = 0;
                                                foreach ($criteria as $label => $max) {
                                                    $total_max += (int)$max;
                                                }
                                                ?>
                                                <div class="alert alert-info">
                                                    <i class="fa fa-info-circle"></i> 
                                                    <strong>Total Maximum Points:</strong> <?= $total_max; ?> points
                                                    <span class="pull-right">
                                                        <strong>Current Total:</strong> <span id="total-display-<?= $entry['id']; ?>">0</span> / <?= $total_max; ?>
                                                    </span>
                                                </div>

                                                <table class="table table-bordered table-hover">
                                                    <thead>
                                                        <tr style="background-color: #e8e8e8;">
                                                            <th width="50%">Criteria</th>
                                                            <th width="20%" class="text-center">Maximum</th>
                                                            <th width="20%" class="text-center">Marks Given</th>
                                                            <th width="10%" class="text-center">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($criteria as $label => $max): 
                                                            $field = strtolower(str_replace(array(' ', '&', '(', ')', '/', '%'), array('_','and','','','_','percent'), $label));
                                                            $val   = isset($saved_points[$field]) ? (int)$saved_points[$field] : 0;
                                                        ?>
                                                            <tr>
                                                                <td>
                                                                    <strong><?= $label; ?></strong>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="badge badge-info"><?= $max; ?></span>
                                                                </td>
                                                                <td class="text-center">
                                                                    <input type="number"
                                                                           name="points[<?= $field; ?>]"
                                                                           class="form-control points-input"
                                                                           data-entry-id="<?= $entry['id']; ?>"
                                                                           data-max="<?= $max; ?>"
                                                                           value="<?= $val; ?>"
                                                                           min="0"
                                                                           max="<?= $max; ?>"
                                                                           style="text-align: center; width: 80px; margin: 0 auto;"
                                                                           required>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="label label-<?= $val > 0 ? 'success' : 'default'; ?>" id="status-<?= $entry['id']; ?>-<?= $field; ?>">
                                                                        <?= $val > 0 ? 'Marked' : 'Pending'; ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr style="background-color: #f0f0f0; font-weight: bold;">
                                                            <td class="text-right"><strong>TOTAL:</strong></td>
                                                            <td class="text-center"><strong><?= $total_max; ?></strong></td>
                                                            <td class="text-center">
                                                                <strong id="total-marks-<?= $entry['id']; ?>">0</strong>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="label label-primary" id="total-status-<?= $entry['id']; ?>">Incomplete</span>
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            <i class="fa fa-exclamation-triangle"></i> No criteria configured for this category.
                                        </div>
                                    <?php endif; ?>
        
                                    <div class="form-group" style="margin-top: 20px;">
                                        <div class="col-sm-12 text-center">
                                            <button type="submit" name="status" value="approved" class="btn btn-success btn-lg" style="margin-right: 10px;">
                                                <i class="fa fa-check-circle"></i> Approve & Save Marks
                                            </button>
                                            <button type="submit" name="status" value="rejected" class="btn btn-danger btn-lg">
                                                <i class="fa fa-times-circle"></i> Reject Entry
                                            </button>
                                        </div>
                                    </div>
        
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
        
            </div>
        </div>
    </div>
</div>

<script>
// Auto-calculate total points as user enters marks
$(document).ready(function() {
    $('.points-input').on('input change', function() {
        var entryId = $(this).data('entry-id');
        var max = parseInt($(this).data('max'));
        var value = parseInt($(this).val()) || 0;
        var field = $(this).attr('name');
        
        // Validate max value
        if (value > max) {
            $(this).val(max);
            value = max;
            alert('Maximum marks for this criteria is ' + max);
        }
        
        // Update status badge for this field
        var fieldName = field.replace('points[', '').replace(']', '');
        var statusBadge = $('#status-' + entryId + '-' + fieldName);
        if (value > 0) {
            statusBadge.removeClass('label-default').addClass('label-success').text('Marked');
        } else {
            statusBadge.removeClass('label-success').addClass('label-default').text('Pending');
        }
        
        // Calculate total for this entry
        calculateTotal(entryId);
    });
    
    // Initialize totals on page load
    $('.points-input').each(function() {
        var entryId = $(this).data('entry-id');
        calculateTotal(entryId);
    });
});

function calculateTotal(entryId) {
    var total = 0;
    var allFilled = true;
    
    $('#judge-form-' + entryId + ' .points-input').each(function() {
        var value = parseInt($(this).val()) || 0;
        total += value;
        if (value == 0) {
            allFilled = false;
        }
    });
    
    $('#total-marks-' + entryId).text(total);
    $('#total-display-' + entryId).text(total);
    
    var totalStatus = $('#total-status-' + entryId);
    if (allFilled && total > 0) {
        totalStatus.removeClass('label-primary').addClass('label-success').text('Complete');
    } else if (total > 0) {
        totalStatus.removeClass('label-success').removeClass('label-primary').addClass('label-warning').text('Partial');
    } else {
        totalStatus.removeClass('label-success').removeClass('label-warning').addClass('label-primary').text('Incomplete');
    }
}

function toggleJudgeBtn(entryId) {
    var btn = $('#judge-btn-' + entryId);
    var panel = $('#entry-' + entryId);
    
    if (panel.hasClass('in')) {
        btn.html('<i class="fa fa-gavel"></i> Judge & Give Marks');
    } else {
        btn.html('<i class="fa fa-chevron-up"></i> Hide Judging Panel');
    }
}

// Form validation before submit
$('form[id^="judge-form-"]').on('submit', function(e) {
    var entryId = $(this).attr('id').replace('judge-form-', '');
    var total = parseInt($('#total-marks-' + entryId).text()) || 0;
    
    if (total == 0) {
        e.preventDefault();
        alert('Please enter marks for at least one criteria before submitting.');
        return false;
    }
    
    var status = $(this).find('button[type="submit"]:focus').val();
    if (status == 'approved' && total == 0) {
        e.preventDefault();
        alert('Cannot approve with zero marks. Please enter marks or reject the entry.');
        return false;
    }
    
    return confirm('Are you sure you want to ' + (status == 'approved' ? 'APPROVE' : 'REJECT') + ' this entry?');
});
</script>

<style>
.points-input:focus {
    border-color: #5cb85c;
    box-shadow: 0 0 5px rgba(92, 184, 92, 0.5);
}

.panel-heading h4 {
    margin: 0;
}

.well {
    background-color: #f9f9f9;
    border: 1px solid #e0e0e0;
}

.table th {
    background-color: #f8f8f8;
}

.badge {
    font-size: 12px;
    padding: 4px 8px;
}
</style>
