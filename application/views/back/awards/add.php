<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-trophy"></i> Add Award Entry</h3>
    </div>

    <div class="panel-body">

        <?php if (isset($success_alert)): ?>
            <div class="alert alert-success"><?= $success_alert; ?></div>
        <?php endif; ?>

        <?php if (isset($danger_alert)): ?>
            <div class="alert alert-danger"><?= $danger_alert; ?></div>
        <?php endif; ?>

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
                    <label class="col-sm-3 control-label">Project/Program Name</label>
                    <div class="col-sm-6">
                        <input type="text" name="form_project_name" class="form-control" placeholder="Required for Program/Project Awards" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Date of Project</label>
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

                <div class="form-group">
                    <label class="col-sm-3 control-label">Supporting Documents</label>
                    <div class="col-sm-6">
                         <input type="file" name="legion_support_doc" class="form-control" required>
                         <span class="help-block">Upload PDF or ZIP (Max 10MB)</span>
                    </div>
                </div>
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
                        <select name="legion_id_individual" id="ind_legion_id" class="form-control" onchange="loadIndMembers(); setIndLegionName()" required>
                            <option value="">Choose Legion</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Member</label>
                    <div class="col-sm-6">
                        <select name="member_id" id="ind_member_id" class="form-control" required>
                            <option value="">Choose Member</option>
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
                    <label class="col-sm-3 control-label">Age</label>
                    <div class="col-sm-2">
                        <input type="number" name="form_age" class="form-control" min="0">
                    </div>

                    <label class="col-sm-1 control-label">Sex</label>
                    <div class="col-sm-2">
                        <input type="text" name="form_sex" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Qualifications</label>
                    <div class="col-sm-3">
                        <input type="text" name="form_qualifications" class="form-control">
                    </div>

                    <label class="col-sm-1 control-label">Vocation</label>
                    <div class="col-sm-3">
                        <input type="text" name="form_vocation" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Marital Status (S/M)</label>
                    <div class="col-sm-3">
                        <input type="text" name="form_marital_status" class="form-control">
                    </div>

                    <label class="col-sm-1 control-label">Name of Spouse</label>
                    <div class="col-sm-3">
                        <input type="text" name="form_spouse_name" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Name(s) of Children</label>
                    <div class="col-sm-6">
                        <input type="text" name="form_children_names" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Date of Local Recognition</label>
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

                <div class="form-group">
                    <label class="col-sm-3 control-label">Passport Photo</label>
                    <div class="col-sm-6">
                         <input type="file" name="individual_photo" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Supporting Documents</label>
                    <div class="col-sm-6">
                         <input type="file" name="individual_support_doc" class="form-control" required>
                         <span class="help-block">Upload PDF or ZIP (Max 10MB)</span>
                    </div>
                </div>
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
var awardCategories = {
    "legion": [
        "OUTSTANDING LEGION",
        "OUTSTANDING NEW LEGION",
        "OUTSTANDING PUBLIC RELATION PROGRAMME",
        "OUTSTANDING COMMUNITY DEVELOPMENT PROGRAMME",
        "OUTSTANDING FAMILY LEGION",
        "OUTSTANDING NATIONAL PROGRAM",
        "OUTSTANDING SENIORETTE WING",
        "OUTSTANDING G&D"
    ],
    "individual": [
        "OUTSTANDING PRESIDENT",
        "OUTSTANDING LEGION OFFICER",
        "OUTSTANDING MEMBER",
        "OUTSTANDING SENIORETTE"
    ]
};

function populateAwardCategories() {
    var legionSelect = $('#legion-category');
    var indSelect = $('#individual-category');
    
    // Clear and keep default
    legionSelect.find('option:gt(0)').remove();
    indSelect.find('option:gt(0)').remove();

    // Populate
    $.each(awardCategories.legion, function(i, val) {
        legionSelect.append($('<option>', { value: val, text: val }));
    });
    $.each(awardCategories.individual, function(i, val) {
        indSelect.append($('<option>', { value: val, text: val }));
    });
}

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
        $('#ind_member_id').html('<option value="">Choose Member</option>');
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
        $('#ind_member_id').html('<option value="">Choose Member</option>'); // reset member
    });
}

function loadIndMembers() {
    var legionId = $('#ind_legion_id').val();
    if (!legionId) {
        $('#ind_member_id').html('<option value="">Choose Member</option>');
        return;
    }
    $.get('<?= base_url('admin/get_members_of_legion'); ?>/' + legionId, function(res) {
        var html = '<option value="">Choose Member</option>';
        try {
            var members = JSON.parse(res);
            for (var i=0; i<members.length; i++) {
                var name = members[i].first_name + ' ' + members[i].last_name;
                html += '<option value="'+members[i].member_id+'">'+name+' ('+(members[i].member_profile_id || '')+')</option>';
            }
        } catch(e) { console.error(e); }
        $('#ind_member_id').html(html);
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
