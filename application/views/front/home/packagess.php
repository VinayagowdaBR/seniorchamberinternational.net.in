<?php
    $home_premium_plans_image = $this->db->get_where('frontend_settings', array('type' => 'home_premium_plans_image'))->row()->value;

    $premium_plans_image = json_decode($home_premium_plans_image, true);
?>
<section class="parallax-section parallax-section-lg pricing-plans pricing-plans--style-1 slice--offset" style="background-image: url(<?=base_url()?>uploads/home_page/premium_plans_image/<?=$premium_plans_image[0]['image']?>)">
    <div class="container">
        <span class="clearfix"></span>55
        <div class="row" style="margin-top: -10px">
            <?php foreach ($all_plans as $value): ?>
                <?php if($value->status == 0) { ?>
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12" style="margin-top: 10px">
                    <?php if ($value->plan_id == 1) { $package_class = "text-line-through"; } else { $package_class = "active"; } ?>
                    <div class="feature feature--boxed-border feature--bg-2 active package_bg">
                        <div class="icon-block--style-1-v5 text-center">
                            <div class="block-icon c-gray-dark">
                                <li style="list-style-type: none;">
                                <?php
                                    $image = $value->image;
                                    $images = json_decode($image, true);
                                    if (file_exists('uploads/plan_image/'.$images[0]['image'])) {
                                    ?>
                                        <img src="<?=base_url()?>uploads/plan_image/<?=$images[0]['image']?>" alt="plan" class="img-sm" height="80">
                                    <?php
                                    }
                                    else {
                                    ?>
                                        <img src="<?=base_url()?>uploads/plan_image/default_image.png" class="img-sm" height="80">
                                    <?php
                                    }
                                ?>
                                </li>
                            </div>
                            <div class="block-content">
                                <h3 class="heading heading-5 strong-500"><?=$value->name?></h3>
                                <h3 class="price-tag" style="font-size: 26px;"><?=currency(isset($value->total_amount) ? $value->total_amount : $value->amount)?></h3>
                                <!--<ul class="pl-0 pr-0 mt-0">-->
                                <!--    <?php if($value->exp_int_status == 0) { ?>-->
                                <!--    <li class="<?=$package_class?> package_items"><?=translate('president:')?> <?=$value->president?> <?=translate('times')?></li>-->
                                <!--    <?php } ?>-->

                                <!--    <?php if($value->dir_msg_status == 0) { ?>-->
                                <!--    <li class="<?=$package_class?> package_items"><?=translate('vice_president')?> <?=$value->vice_president?> <?=translate('times')?></li>-->
                                <!--    <?php } ?>-->
                                <!--    <li class="<?=$package_class?> package_items"><?=translate('secretary')?> <?=$value->secretary?> <?=translate('images')?></li>-->
                                <!--    <li class="<?=$package_class?> package_items"><?=translate('treasurer')?> <?=$value->treasurer?> <?=translate('video')?></li> -->
                                <!--</ul>-->
                                <div class="py-2 text-center mb-2">
                                    <?php
                                    if ($value->plan_id != 1) {
                                        $purchase_link = base_url()."home/plans/subscribe/".$value->plan_id;
                                    }
                                    else {
                                        $purchase_link = "#";
                                    }
                                    ?>
                                    <a href="<?=$purchase_link?>" class="btn btn-styled btn-sm btn-base-1 btn-outline btn-circle z-depth-2-bottom">
                                        <span class="<?=$package_class?>"><?php echo translate('pay_now')?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            <?php endforeach ?>
        </div>
    </div>
</section>
