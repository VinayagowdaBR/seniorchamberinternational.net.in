<div id="content-container">
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header text-overflow">Add Award Entry</h1>
        </div>
        <ol class="breadcrumb">
            <li><a href="#"><?= translate('home')?></a></li>
            <li><a href="#">Awards</a></li>
            <li class="active">Add New</li>
        </ol>
    </div>
    <div id="page-content">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-trophy"></i> Add Award Entry</h3>
            </div>

            <div class="panel-body">

                <?php if (isset($success_alert)): ?>
                    <div class="alert alert-success" id="success_alert"><?= $success_alert; ?></div>
                <?php endif; ?>

                <?php if (isset($danger_alert)): ?>
                    <div class="alert alert-danger" id="danger_alert"><?= $danger_alert; ?></div>
                <?php endif; ?>

<script>
    setTimeout(function() {
        $('#success_alert').fadeOut('fast');
        $('#danger_alert').fadeOut('fast');
    }, 5000); 
</script>

                <form class="form-horizontal" method="post" action="<?= base_url('admin/award/do_add'); ?>" id="award-form" enctype="multipart/form-data">

            <div class="form-group">
                <label class="col-sm-3 control-label">Year</label>
                <div class="col-sm-6">
                    <input type="number" name="year" value="<?= date('Y'); ?>" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label">Award For</label>
                <div class="col-sm-6">
                    <label class="radio-inline">
                        <input type="radio" name="award_for" value="legion" checked onclick="toggleAwardType()"> Group (Legion)
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="award_for" value="individual" onclick="toggleAwardType()"> Individual
                    </label>
                </div>
            </div>

            <!-- Legion section -->
            <div id="legion-section">
                <div class="form-group">
                    <label class="col-sm-3 control-label">Award Category</label>
                    <div class="col-sm-6">
                        <select name="category" id="legion-category" class="form-control" required>
                            <option value="">Choose Legion Award</option>
                        </select>
                    </div>
                </div>

                <!-- New Project Fields -->
                <div class="form-group">
                    <label class="col-sm-3 control-label">Bidding Date</label>
                    <div class="col-sm-3">
                        <input type="date" name="form_project_date" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Area</label>
                    <div class="col-sm-6">
                        <select name="area_id" id="area_id" class="form-control" onchange="loadLegions()" required>
                            <option value="">Choose Area</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= $area['id']; ?>"><?= $area['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Legion</label>
                    <div class="col-sm-6">
                        <select name="legion_id" id="legion_id" class="form-control" onchange="setLegionName()" required>
                            <option value="">Choose Legion</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Legion Name (Display)</label>
                    <div class="col-sm-6">
                        <input type="text" name="legion_name" id="legion_name" class="form-control" readonly>
                    </div>
                </div>

                <!-- Award manual fields for Legion entry form -->
                <div class="form-group">
                    <label class="col-sm-3 control-label">Name of the Award (printed)</label>
                    <div class="col-sm-6">
                        <input type="text" name="form_award_name" class="form-control"
                               placeholder="As to be printed on certificate (if applicable)">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Legion President Name</label>
                    <div class="col-sm-6">
                        <input type="text" name="form_president_name" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Address of Legion</label>
                    <div class="col-sm-6">
                        <textarea name="form_legion_address" class="form-control" rows="3" required></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Total Membership Strength</label>
                    <div class="col-sm-3">
                        <input type="number" name="form_members_count" class="form-control" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Date of Affiliation</label>
                    <div class="col-sm-3">
                        <input type="date" name="form_affiliation_date" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Description / Major Achievements</label>
                    <div class="col-sm-6">
                        <textarea name="form_major_achievements_legion" class="form-control" rows="5" placeholder="Describe the activities, impact, and results..." required></textarea>
                    </div>
                </div>

                <!-- Dynamic Criteria Section -->
                <div id="legion-criteria-container"></div>


            </div>

            <!-- Individual section -->
            <div id="individual-section" style="display:none;">
                <div class="form-group">
                    <label class="col-sm-3 control-label">Award Category</label>
                    <div class="col-sm-6">
                        <select name="category" id="individual-category" class="form-control" required>
                            <option value="">Choose Individual Award</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Area</label>
                    <div class="col-sm-6">
                        <select id="ind_area_id" class="form-control" onchange="loadIndLegions()" required>
                            <option value="">Choose Area</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= $area['id']; ?>"><?= $area['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Legion</label>
                    <div class="col-sm-6">
                        <select name="legion_id_individual" id="ind_legion_id" class="form-control" onchange="setIndLegionName()" required>
                            <option value="">Choose Legion</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Name of Nominee</label>
                    <div class="col-sm-6">
                        <input type="text" name="nominee_name" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Proposed By (Name)</label>
                    <div class="col-sm-6">
                        <input type="text" name="form_proposed_by" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Legion Name</label>
                    <div class="col-sm-6">
                        <input type="text" name="legion_name_individual" id="legion_name_individual" class="form-control" readonly required>
                    </div>
                </div>

                <!-- RAC National Award nomination fields -->
                <div class="form-group">
                    <label class="col-sm-3 control-label">Year of Charter</label>
                    <div class="col-sm-3">
                        <input type="text" name="form_year_of_charter" class="form-control" required>
                    </div>
                    <label class="col-sm-1 control-label">Member Since</label>
                    <div class="col-sm-2">
                        <input type="text" name="form_member_since" class="form-control" placeholder="Year" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Mailing Address</label>
                    <div class="col-sm-6">
                        <textarea name="form_mailing_address" class="form-control" rows="3"></textarea>
                    </div>
                </div>



                <div class="form-group">
                    <label class="col-sm-3 control-label">Date of Award Bidding</label>
                    <div class="col-sm-3">
                        <input type="date" name="form_legion_award_date" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Bio / Citation (Short)</label>
                    <div class="col-sm-6">
                        <textarea name="form_bio" class="form-control" rows="3" placeholder="Short bio for emcee to read if awarded (approx 100 words)" required></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Description of Achievements</label>
                    <div class="col-sm-6">
                        <textarea name="form_major_achievements" class="form-control" rows="5" placeholder="Detailed list of achievements justifying this nomination" required></textarea>
                    </div>
                </div>

                <!-- Dynamic Criteria Section -->
                <div id="individual-criteria-container"></div>


            </div>

            <hr>

            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <button type="submit" class="btn btn-primary btn-block">
                        Save Award Entry
                    </button>
                </div>
            </div>

        </form>

    </div>
</div>

<script>
var awardDetailMap = <?= json_encode($award_criteria); ?>;

function populateAwardCategories() {
    var legionSelect = $('#legion-category');
    var indSelect = $('#individual-category');
    
    // Clear and keep default
    legionSelect.find('option:gt(0)').remove();
    indSelect.find('option:gt(0)').remove();

    // Populate from Dynamic Map
    if (awardDetailMap.legion) {
        $.each(Object.keys(awardDetailMap.legion), function(i, val) {
            legionSelect.append($('<option>', { value: val, text: val }));
        });
    }
    if (awardDetailMap.individual) {
        $.each(Object.keys(awardDetailMap.individual), function(i, val) {
            indSelect.append($('<option>', { value: val, text: val }));
        });
    }
}

// Logic to render criteria fields
function renderCriteria(type, category) {
    var container = (type === 'legion') ? $('#legion-criteria-container') : $('#individual-criteria-container');
    container.empty();

    if (!category || !awardDetailMap[type] || !awardDetailMap[type][category]) {
        return;
    }

    var criteriaList = awardDetailMap[type][category]; // Object: { "Criteria Name": "Points" (int) }
    
    container.append('<h4>Category Criteria Requirements</h4><hr>');

    var i = 0;
    $.each(criteriaList, function(criteriaName, points) {
        var html = `
            <div class="form-group criteria-group">
                <label class="col-sm-3 control-label">${criteriaName} <br><small class="text-muted">(Max ${points} pts)</small></label>
                <div class="col-sm-6">
                    <input type="hidden" name="criteria[${i}][name]" value="${criteriaName}">
                    <input type="hidden" name="criteria[${i}][max_points]" value="${points}">
                    
                    <textarea name="criteria[${i}][desc]" class="form-control" rows="3" placeholder="Description and justification for ${criteriaName}" required></textarea>
                    <div style="margin-top: 5px;">
                        <label>Upload Evidence (2 Images):</label>
                        <input type="file" name="criteria_files_${i}[]" multiple accept="image/*" class="form-control">
                        <span class="help-block">Please upload exactly 2 images proving this criterion.</span>
                    </div>
                </div>
            </div>
        `;
        container.append(html);
        i++;
    });
}

// Event Listeners for render
$('#legion-category').change(function() {
    renderCriteria('legion', $(this).val());
});

$('#individual-category').change(function() {
    renderCriteria('individual', $(this).val());
});

function toggleAwardType() {
    var val = $('input[name="award_for"]:checked').val();
    if (val === 'legion') {
        $('#legion-section').show();
        $('#individual-section').hide();
        
        // Disable individual fields so they aren't validated
        $('#individual-section').find('input, select, textarea').prop('disabled', true);
        $('#legion-section').find('input, select, textarea').prop('disabled', false);

        $('#legion-category').attr('required', true);
    } else {
        $('#legion-section').hide();
        $('#individual-section').show();
        
        // Disable legion fields
        $('#legion-section').find('input, select, textarea').prop('disabled', true);
        $('#individual-section').find('input, select, textarea').prop('disabled', false);

        $('#individual-category').attr('required', true);
    }
}

function loadLegions() {
    var areaId = $('#area_id').val();
    if (!areaId) {
        $('#legion_id').html('<option value="">Choose Legion</option>');
        return;
    }
    $.get('<?= base_url('admin/get_legions_of_area'); ?>/' + areaId, function(res) {
        var html = '<option value="">Choose Legion</option>';
        try {
            var legions = JSON.parse(res);
            for (var i=0; i<legions.length; i++) {
                html += '<option value="'+legions[i].id+'" data-name="'+legions[i].name+'">'+legions[i].name+' ('+(legions[i].prefix || '')+')</option>';
            }
        } catch(e) {}
        $('#legion_id').html(html);
    });
}

function setLegionName() {
    var name = $('#legion_id option:selected').data('name') || '';
    $('#legion_name').val(name);
}


function loadIndLegions() {
    var areaId = $('#ind_area_id').val();
    if (!areaId) {
        $('#ind_legion_id').html('<option value="">Choose Legion</option>');
        return;
    }
    $.get('<?= base_url('admin/get_legions_of_area'); ?>/' + areaId, function(res) {
        var html = '<option value="">Choose Legion</option>';
        try {
            var legions = JSON.parse(res);
            for (var i=0; i<legions.length; i++) {
                html += '<option value="'+legions[i].id+'" data-name="'+legions[i].name+'">'+legions[i].name+' ('+(legions[i].prefix || '')+')</option>';
            }
        } catch(e) {}
        $('#ind_legion_id').html(html);
    });
}



function setIndLegionName() {
    var name = $('#ind_legion_id option:selected').data('name') || '';
    $('#legion_name_individual').val(name);
}

$(document).ready(function() {
    populateAwardCategories();
    toggleAwardType();
});
</script>
    </div>
</div>
