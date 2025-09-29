<section class="slice sct-color-1">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <h2 class="heading heading-2 strong-400">
                                <?php echo translate('confirm_your_purchase')?>
                            </h2>
                            <div class="fluid-paragraph fluid-paragraph-sm c-gray-light strong-300">
                                <p><?php echo translate('please_check_your_package_and_payment_method_then_click_the_button_bellow')?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <span class="space-xs-xl"></span>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="feature feature--boxed-border feature--bg-2 active">
                            <div class="feature-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <h3 class="heading heading-6 strong-500">
                                            <?php echo translate('package_summary')?>
                                        </h3>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <tbody>
                                                    <tr>
                                                        <td class="c-gray-light"><?php echo translate('package')?>:</td>
                                                        <td class="strong-600 text-right"><?php echo $selected_plan[0]->name?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="c-gray-light"><?php echo translate('price')?>:</td>
                                                        <td class="strong-600 text-right"><?php echo currency('', 'def') . $selected_plan[0]->amount?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="c-gray-light"><?php echo translate('gst')?> (<?php echo $selected_plan[0]->gst?>%):</td>
                                                        <td class="strong-600 text-right"><?php echo currency('', 'def') . ($selected_plan[0]->amount * $selected_plan[0]->gst / 100)?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="text-right">
                                            <h4 class="heading heading-5 strong-500">
                                                <?php echo translate('total')?>: <?php echo currency('', 'def') . $selected_plan[0]->total_amount?>
                                            </h4>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <h3 class="heading heading-6 strong-500">
                                            <?php echo translate('payment_method')?>
                                        </h3>
                                        <form class="form-default" id="payment_form" method="post" action="<?php echo base_url()?>home/process_payment">
                                            <input type="hidden" name="plan_id" value="<?php echo $selected_plan[0]->plan_id?>">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="control-label"><?php echo translate('select_a_payment_method')?></label>
                                                        <div class="form-group">
                                                            <?php
                                                                $paypal_set = $this->db->get_where('business_settings', array('type' => 'paypal_set'))->row()->value;
                                                                $stripe_set = $this->db->get_where('business_settings', array('type' => 'stripe_set'))->row()->value;
                                                                $pum_set = $this->db->get_where('business_settings', array('type' => 'pum_set'))->row()->value;
                                                                $instamojo_set = $this->db->get_where('business_settings', array('type' => 'instamojo_set'))->row()->value;
                                                                $phonepe_set = $this->db->get_where('business_settings', array('type' => 'phonepe_set'))->row()->value;
                                                            ?>
                                                            <div class="radio-list">
                                                                <?php if ($paypal_set == 'ok'): ?>
                                                                    <label class="radio radio-inline">
                                                                        <input type="radio" name="payment_type" value="paypal" class="payment-type" required> <?php echo translate('paypal')?>
                                                                    </label>
                                                                <?php endif; ?>
                                                                
                                                                <?php if ($stripe_set == 'ok'): ?>
                                                                    <label class="radio radio-inline">
                                                                        <input type="radio" name="payment_type" value="stripe" class="payment-type" required> <?php echo translate('stripe')?>
                                                                    </label>
                                                                <?php endif; ?>
                                                                
                                                                <?php if ($pum_set == 'ok'): ?>
                                                                    <label class="radio radio-inline">
                                                                        <input type="radio" name="payment_type" value="pum" class="payment-type" required> <?php echo translate('payumoney')?>
                                                                    </label>
                                                                <?php endif; ?>
                                                                
                                                                <?php if ($instamojo_set == 'ok'): ?>
                                                                    <label class="radio radio-inline">
                                                                        <input type="radio" name="payment_type" value="instamojo" class="payment-type" required> <?php echo translate('instamojo')?>
                                                                    </label>
                                                                <?php endif; ?>
                                                                
                                                                <!-- ✅ ADD OR VERIFY PHONEPE OPTION HERE -->
                                                                <?php if ($phonepe_set == 'ok'): ?>
                                                                    <label class="radio radio-inline">
                                                                        <input type="radio" name="payment_type" value="phonepe" class="payment-type" required checked> <?php echo translate('phonepe')?>
                                                                    </label>
                                                                <?php endif; ?>
                                                                <!-- ✅ END PHONEPE OPTION -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <button type="submit" class="btn btn-base-1 btn-circle btn-icon-right">
                                                    <span><?php echo translate('confirm_and_pay')?></span>
                                                </button>
                                            </div>
                                        </form>
                                        <script>
                                        (function(){
                                            var form = document.getElementById('payment_form');
                                            if (!form) return;
                                            var radios = form.querySelectorAll('input[name="payment_type"]');
                                            var anyChecked = false;
                                            for (var i = 0; i < radios.length; i++) { if (radios[i].checked) { anyChecked = true; break; } }
                                            if (!anyChecked && radios.length > 0) { radios[0].checked = true; }
                                            form.addEventListener('submit', function(e){
                                                var selected = form.querySelector('input[name="payment_type"]:checked');
                                                if (!selected) {
                                                    e.preventDefault();
                                                    alert('Please select a payment method.');
                                                }
                                            });
                                        })();
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
