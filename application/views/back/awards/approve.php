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
                    <p class="text-muted text-center">No award entries found.</p>
                <?php endif; ?>
        
                <?php $idx = 1; foreach ($entries as $entry): 
                    $criteria     = get_entry_criteria($award_criteria, $entry);
                    $saved_points = json_decode($entry['points_json'], true) ?: array();
                ?>
                    <div class="panel panel-default">
                        <div class="panel-heading" data-toggle="collapse" href="#entry-<?= $entry['id']; ?>" style="cursor:pointer;">
                            <strong>#<?= $idx++; ?> - <?= $entry['category']; ?> (<?= ucfirst($entry['award_for']); ?>)</strong>
                            &nbsp;Year: <?= $entry['year']; ?> |
                            <?php if ($entry['award_for'] == 'legion'): ?>
                                Legion: <?= $entry['legion_name']; ?>
                            <?php else: ?>
                                Nominee: <?= $entry['nominee_name']; ?>
                            <?php endif; ?>
                            &nbsp;| Status:
                            <span class="label label-<?= $entry['status']=='approved'?'success':($entry['status']=='rejected'?'danger':'warning'); ?>">
                                <?= ucfirst($entry['status']); ?>
                            </span>
                            &nbsp;| Total: <strong><?= (int)$entry['total_points']; ?></strong>
                        </div>
        
                        <div id="entry-<?= $entry['id']; ?>" class="panel-collapse collapse">
                            <div class="panel-body">
                                <form class="form-horizontal" method="post" action="<?= base_url('admin/award/update_status/'.$entry['id']); ?>">
        
                                    <?php if (!empty($criteria)): ?>
                                        <?php foreach ($criteria as $label => $max): 
                                            $field = strtolower(str_replace(array(' ', '&', '(', ')', '/', '%'), array('_','and','','','_','percent'), $label));
                                            $val   = isset($saved_points[$field]) ? (int)$saved_points[$field] : 0;
                                        ?>
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label"><?= $label; ?> (max <?= $max; ?>)</label>
                                                <div class="col-sm-4">
                                                    <input type="number"
                                                           name="points[<?= $field; ?>]"
                                                           class="form-control"
                                                           value="<?= $val; ?>"
                                                           min="0"
                                                           max="<?= $max; ?>">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-warning">No criteria configured for this category.</p>
                                    <?php endif; ?>
        
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">Action</label>
                                        <div class="col-sm-8">
                                            <button type="submit" name="status" value="approved" class="btn btn-success">
                                                <i class="fa fa-check"></i> Approve & Save Marks
                                            </button>
                                            <button type="submit" name="status" value="rejected" class="btn btn-danger">
                                                <i class="fa fa-times"></i> Reject
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
