<style>
    .custom-card {
        background: #fff;
        border-radius: 8px;
        padding: 25px;
        box-shadow: 0px 2px 10px rgba(0,0,0,0.08);
        margin-top: 20px;
    }
    .custom-label {
        font-weight: 600;
        padding-top: 7px;
    }
    .page-header {
        margin-top: 20px !important;
    }
</style>

<div id="content-container">
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header"><?= translate('add_member') ?></h1>
        </div>

        <ol class="breadcrumb">
            <li><a href="<?= base_url() ?>admin"><?= translate('home') ?></a></li>
            <li><a href="#"><?= translate('members') ?></a></li>
            <li><a href="#"><?= translate('add_member') ?></a></li>
        </ol>
    </div>

    <div id="page-content">
        <div class="custom-card">

            <!-- SUCCESS MESSAGE -->
            <?php if (!empty($success_alert)): ?>
                <div class="alert alert-success" id="success_alert">
                    <button class="close" data-dismiss="alert"><i class="pci-cross pci-circle"></i></button>
                    <?= $success_alert ?>
                </div>
            <?php endif; ?>

            <!-- ERROR MESSAGES -->
            <?php if (!empty(validation_errors())): ?>
                <div class="alert alert-danger" id="danger_alert">
                    <button class="close" data-dismiss="alert"><i class="pci-cross pci-circle"></i></button>
                    <?= validation_errors() ?>
                </div>
            <?php endif; ?>

            <div class="panel-heading" style="margin-bottom: 20px;">
                <a href="javascript:history.back()" class="btn btn-danger btn-sm btn-labeled fa fa-step-backward" style="float:right;">
                    Go Back
                </a>
                <h3 class="panel-title"><?= translate('add_new_member_info') ?></h3>
                <div style="clear:both;"></div>
            </div>

            <form class="form-horizontal" id="manage_details_form" method="POST" action="<?=base_url()?>admin/members/add_member/do_add">

                <!-- FIRST NAME -->
                <div class="form-group">
                    <label class="col-sm-3 control-label custom-label"><?= translate('first_name') ?> *</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="fname"
                               value="<?= !empty($form_contents) ? $form_contents['fname'] : '' ?>"
                               placeholder="<?= translate('first_name') ?>">
                    </div>
                </div>

                <!-- LAST NAME -->
                <div class="form-group">
                    <label class="col-sm-3 control-label custom-label"><?= translate('last_name') ?> *</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="lname"
                               value="<?= !empty($form_contents) ? $form_contents['lname'] : '' ?>"
                               placeholder="<?= translate('last_name') ?>" required>
                    </div>
                </div>

                <!-- EMAIL -->
                <div class="form-group">
                    <label class="col-sm-3 control-label custom-label"><?= translate('email') ?> *</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="email"
                               value="<?= !empty($form_contents) ? $form_contents['email'] : '' ?>"
                               placeholder="<?= translate('email') ?>" required>
                    </div>
                </div>

                <!-- GENDER -->
                <div class="form-group">
                    <label class="col-sm-3 control-label custom-label"><?= translate('gender') ?> *</label>
                    <div class="col-sm-8">
                        <?php
                            if (!empty($form_contents)) {
                                echo $this->Crud_model->select_html('gender', 'gender', 'name', 'edit', 'form-control selectpicker', $form_contents['gender']);
                            } else {
                                echo $this->Crud_model->select_html('gender', 'gender', 'name', 'add', 'form-control selectpicker');
                            }
                        ?>
                    </div>
                </div>

                <!-- DATE OF BIRTH -->
                <div class="form-group">
                    <label class="col-sm-3 control-label custom-label"><?= translate('date_of_birth') ?> *</label>
                    <div class="col-sm-8">
                        <input type="date" class="form-control" name="date_of_birth"
                               value="<?= !empty($form_contents) ? $form_contents['date_of_birth'] : '' ?>">
                    </div>
                </div>

                <!-- MOBILE -->
                <div class="form-group">
                    <label class="col-sm-3 control-label custom-label"><?= translate('mobile') ?> *</label>
                    <div class="col-sm-8">
                        <input type="number" class="form-control" name="mobile"
                               value="<?= !empty($form_contents) ? $form_contents['mobile'] : '' ?>"
                               placeholder="<?= translate('mobile_no.') ?>">
                    </div>
                </div>

                <!-- PLAN -->
                <div class="form-group">
                    <label class="col-sm-3 control-label custom-label"><?= translate('plan') ?> *</label>
                    <div class="col-sm-8">
                        <?php
                            if (!empty($form_contents)) {
                                echo $this->Crud_model->select_html('plan', 'plan', 'name', 'edit', 'form-control selectpicker', $form_contents['plan']);
                            } else {
                                echo $this->Crud_model->select_html('plan', 'plan', 'name', 'add', 'form-control selectpicker');
                            }
                        ?>
                    </div>
                </div>

				<!-- <select class="form-control" name="membership" required>
						<option value="">Select Membership*</option>
						<option value="0">Guest</option>
						<option value="1">Visitor</option>
						<option value="2">Legion Member</option>
						<option value="3">National Member</option>
						<option value="4">NGB Member</option>
					</select> -->

					<!-- MEMBERSHIP -->
					<div class="form-group">
						<label class="col-sm-3 control-label custom-label">Membership *</label>
						<div class="col-sm-8">
							<select class="form-control" name="membership" required>
								<option value="">Select Membership*</option>
								<option value="0">Guest</option>
								<option value="1">Visitor</option>
								<option value="2">Legion Member</option>
								<option value="3">National Member</option>
								<option value="4">NGB Member</option>
							</select>
						</div>
					</div>


                <!-- AREA -->
                <div class="form-group">
                    <label class="col-sm-3 control-label custom-label">Area *</label>
                    <div class="col-sm-8">
                        <select id="area" name="area_id" class="form-control" onchange="getLegions(this.value); setAreaName(this);">
                            <option value="">Select Area</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= $area['id'] ?>" <?= ($area['id'] == $selected_area) ? 'selected' : '' ?>>
                                    <?= $area['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="area" id="area_name">
                    </div>
                </div>

                <!-- LEGION -->
                <div class="form-group">
                    <label class="col-sm-3 control-label custom-label">Legion *</label>
                    <div class="col-sm-8">
                        <select id="legion" name="legion_id" class="form-control" onchange="setLegionName(this);">
                            <option value="">Select Legion</option>
                        </select>
                        <input type="hidden" name="legion" id="legion_name">
                    </div>
                </div>

                <!-- PASSWORD -->
                <div class="form-group">
                    <label class="col-sm-3 control-label custom-label"><?= translate('password') ?> *</label>
                    <div class="col-sm-8">
                        <input type="password" class="form-control" name="password">
                    </div>
                </div>

                <!-- CONFIRM PASSWORD -->
                <div class="form-group">
                    <label class="col-sm-3 control-label custom-label"><?= translate('confirm_password') ?> *</label>
                    <div class="col-sm-8">
                        <input type="password" class="form-control" name="cpassword">
                    </div>
                </div>

                <!-- SUBMIT BUTTON -->
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-8 text-right">
                        <button type="submit" class="btn btn-primary btn-sm btn-labeled fa fa-save">
                            <?= translate('add_member') ?>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
setTimeout(() => {
    $('#success_alert').fadeOut('fast');
    $('#danger_alert').fadeOut('fast');
}, 5000);

function getLegions(areaId) {
    if (!areaId) {
        document.getElementById('legion').innerHTML = '<option value="">Select Legion</option>';
        return;
    }

    fetch("<?= base_url('admin/get_legions_of_area/') ?>" + areaId)
        .then(res => res.json())
        .then(data => {
            let options = '<option value="">Select Legion</option>';
            data.forEach(l => {
                options += `<option value="${l.id}">${l.name}</option>`;
            });
            document.getElementById('legion').innerHTML = options;
        })
        .catch(err => console.error(err));
}

function setAreaName(sel) {
    document.getElementById('area_name').value = sel.options[sel.selectedIndex].text;
}

function setLegionName(sel) {
    document.getElementById('legion_name').value = sel.options[sel.selectedIndex].text;
}
</script>
