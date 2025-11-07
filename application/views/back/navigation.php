<!-- MAIN NAVIGATION -->
<nav id="mainnav-container">
    <div id="mainnav">
        <!-- Menu -->
        <div id="mainnav-menu-wrap">
            <div class="nano">
                <div class="nano-content">
                    <ul id="mainnav-menu" class="list-group">
                        <li <?php if($page_name=="dashboard") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin">
                                <i class="fa fa-home"></i>
                                <span class="menu-title"><?php echo translate('dashboard')?></span>
                            </a>
                        </li>

                        <?php if ($this->Crud_model->admin_permission('area_legion')) { ?>
                        <li <?php if($page_name=="area_legion") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/area_legion">
                                <i class="fa fa-globe"></i>
                                <span class="menu-title"><?php echo translate('area_&_legions')?></span>
                            </a>
                        </li>
                        <?php } ?>

                        <!-- <?php if ($this->Crud_model->admin_permission('members')) { ?>
                        <li <?php if(in_array($page_name, ['free_members', 'premium_members', 'national_members', 'ngb_members', 'guest_members', 'deleted_member', 'add_member', 'bulk_member_add', 'member_profile_pic_approval'])) echo 'class="active-sub active"'; ?>>
                            <a href="#">
                                <i class="fa fa-users"></i>
                                <span class="menu-title"><?php echo translate('members')?></span>
                                <i class="arrow"></i>
                            </a>
                            <ul class="collapse">
                                <?php if ($this->Crud_model->admin_permission('free_members')) { ?>
                                <li <?php if($page_name=="free_members") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/members/free_members"><i class="fa fa-user-o"></i><?php echo translate('visitors')?></a>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('guest_members')) { ?>
                                <li <?php if($page_name=="guest_members") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/members/guest_members"><i class="fa fa-user-o"></i><?php echo translate('Guests')?></a>
                                </li>

                                <?php } if ($this->Crud_model->admin_permission('ngb_members')) { ?>
                                <li <?php if($page_name=="ngb_members") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/members/ngb_members"><i class="fa fa-user-o"></i><?php echo translate('NGB Members')?></a>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('national_members')) { ?>
                                <li <?php if($page_name=="national_members") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/members/national_members"><i class="fa fa-user-o"></i><?php echo translate('National Members')?></a>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('premium_members')) { ?>
                                <li <?php if($page_name=="premium_members") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/members/premium_members"><i class="fa fa-user-o"></i><?php echo translate('Legion Members')?></a>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('add_members')) { ?>
                                <li <?php if($page_name=="add_member") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/members/add_member"><i class="fa fa-address-card"></i><?php echo translate('add_member')?></a>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('bulk_member_add')) { ?>
                                <li <?php if($page_name=="bulk_member_add") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/bulk_member_add"><i class="fa fa-address-card"></i><?php echo translate('bulk_member_add')?></a>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('deleted_members')) { ?>
                                <li <?php if($page_name=="deleted_member") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/deleted_members"><i class="fa fa-user-times"></i><?php echo translate('deleted_members')?></a>
                                </li>
                                <?php } ?>
                                <?php if ($this->Crud_model->admin_permission('member_profile_pic_approval') && $this->db->get_where('general_settings', ['type' => 'member_profile_pic_approval_by_admin'])->row()->value == 'on') { ?>
                                <li <?php if($page_name=="member_profile_pic_approval") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/member_profile_image_approval"><i class="fa fa-image"></i><?php echo translate('profile_pic_approval')?></a>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>
                        <?php } ?> -->



                        <?php 
// Get active membership types
$CI =& get_instance();
$membership_types = $CI->db->order_by('display_order', 'ASC')
                           ->get_where('membership_types', array('status' => 'active'))
                           ->result();

$member_pages = array('free_members', 'premium_members', 'national_members', 'ngb_members', 
                     'guest_members', 'deleted_member', 'add_member', 'bulk_member_add', 
                     'member_profile_pic_approval');
?>

<?php if ($this->Crud_model->admin_permission('members')) { ?>
<li <?php if(in_array($page_name, $member_pages)) echo 'class="active-sub active"'; ?>>
    <a href="#">
        <i class="fa fa-users"></i>
        <span class="menu-title"><?php echo translate('members')?></span>
        <i class="arrow"></i>
    </a>
    <ul class="collapse">
        <?php 
        // DYNAMIC membership type links
        foreach ($membership_types as $membership) { 
            if ($this->Crud_model->admin_permission($membership->slug)) { 
        ?>
        <li <?php if($page_name == $membership->slug) echo 'class="active-link"'; ?>>
            <a href="<?=base_url()?>admin/members/<?=$membership->slug?>">
                <i class="fa fa-user-o"></i>
                <?php echo translate($membership->name)?>
            </a>
        </li>
        <?php 
            } 
        } 
        ?>
        
        <!-- Static menu items (add, bulk add, deleted, etc.) -->
        <?php if ($this->Crud_model->admin_permission('add_members')) { ?>
        <li <?php if($page_name=="add_member") echo 'class="active-link"'; ?>>
            <a href="<?=base_url()?>admin/members/add_member">
                <i class="fa fa-address-card"></i>
                <?php echo translate('add_member')?>
            </a>
        </li>
        <?php } ?>
        
        <?php if ($this->Crud_model->admin_permission('bulk_member_add')) { ?>
        <li <?php if($page_name=="bulk_member_add") echo 'class="active-link"'; ?>>
            <a href="<?=base_url()?>admin/bulk_member_add">
                <i class="fa fa-address-card"></i>
                <?php echo translate('bulk_member_add')?>
            </a>
        </li>
        <?php } ?>
        
        <?php if ($this->Crud_model->admin_permission('deleted_members')) { ?>
        <li <?php if($page_name=="deleted_member") echo 'class="active-link"'; ?>>
            <a href="<?=base_url()?>admin/deleted_members">
                <i class="fa fa-user-times"></i>
                <?php echo translate('deleted_members')?>
            </a>
        </li>
        <?php } ?>

        <?php if ($this->Crud_model->admin_permission('membership_management')) { ?>
            <li <?php if($page_name == 'membership_list' || $page_name == 'add_membership' || $page_name == 'edit_membership') echo 'class="active"'; ?>>
                <a href="<?=base_url()?>admin/membership_management">
                    <i class="fa fa-id-card"></i>
                    <span class="menu-title"><?php echo translate('membership_types')?></span>
                </a>
            </li>
            <?php } ?>
       
        <?php if ($this->Crud_model->admin_permission('member_profile_pic_approval') && 
                  $this->db->get_where('general_settings', ['type' => 'member_profile_pic_approval_by_admin'])->row()->value == 'on') { ?>
        <li <?php if($page_name=="member_profile_pic_approval") echo 'class="active-link"'; ?>>
            <a href="<?=base_url()?>admin/member_profile_image_approval">
                <i class="fa fa-image"></i>
                <?php echo translate('profile_pic_approval')?>
            </a>
        </li>
        <?php } ?>
    </ul>
</li>
<?php } ?>














                        <?php if ($this->Crud_model->admin_permission('premium_plans')) { ?>
                        <li <?php if($page_name=="packages") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/packages">
                                <i class="fa fa-globe"></i>
                                <span class="menu-title"><?php echo translate('dues_payment')?></span>
                            </a>
                        </li>
                        <?php } ?>

                        <?php if ($this->Crud_model->admin_permission('contribution')) { ?>
                        <li <?php if($page_name=="contribution") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/contribution">
                                <i class="fa fa-globe"></i>
                                <span class="menu-title"><?php echo translate('contribution')?></span>
                            </a>
                        </li>
                        <?php } ?>

                        <?php if ($this->Crud_model->admin_permission('channel_partner')) { ?>
                        <li <?php if($page_name=="channel_partner") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/channel_partner">
                                <i class="fa fa-toggle-right"></i>
                                <span class="menu-title"><?php echo translate('committee_members')?></span>
                            </a>
                        </li>
                        <?php } ?>

                        <?php if ($this->Crud_model->admin_permission('blogs')) { ?>
                        <li <?php if($page_name=="blog_page") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/blog">
                                <i class="fa fa-vcard"></i>
                                <span class="menu-title"><?php echo translate('blogs')?></span>
                            </a>
                        </li>
                        <li <?php if($page_name=="SCI_photo") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/guruji_photos">
                                <i class="fa fa-picture-o"></i>
                                <span class="menu-title"><?php echo translate('SCI_photo')?></span>
                            </a>
                        </li>
                        <?php } ?>

                        <?php if ($this->Crud_model->admin_permission('reporting')) { ?>
                        <li <?php if($page_name=="stories") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/stories">
                                <i class="fa fa-picture-o"></i>
                                <span class="menu-title"><?php echo translate('Projects')?></span>
                            </a>
                        </li>
                        <?php } ?>

                        <?php if ($this->Crud_model->admin_permission('earnings')) { ?>
                        <li <?php if($page_name=="earnings") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/earnings">
                                <i class="fa fa-usd"></i>
                                <span class="menu-title"><?php echo translate('earnings')?></span>
                            </a>
                        </li>
                        <?php } ?>

<!-- //////////////////////////////////////////// BULK PAYMETN  NAVEBAR CRATING START  /////////////////////////////////////////// -->

                <?php if ($this->Crud_model->admin_permission('bulkpayment')) { ?>
                    <!-- Bulk Payment Parent Menu with Dropdown -->
                    <li class="parent <?php if($page_name=="bulkpayment" || $page_name=="bulkpayment/invoice_list" || $page_name=="bulkpayment/admin_invoices" || $page_name=="bulkpayment/invoice_detail") echo 'active-sub'; ?>">
                        <a href="#">
                            <i class="fa fa-money"></i>
                            <span class="menu-title"><?php echo translate('bulk_payment'); ?></span>
                        </a>
                        <ul class="children">
                            <!-- Make Bulk Payment -->
                            <li <?php if($page_name=="bulkpayment") echo 'class="active-link"'; ?>>
                                <a href="<?=base_url()?>admin/bulkpayment">
                                    <i class="fa fa-credit-card"></i>
                                    <span class="menu-title"><?php echo translate('make_payment'); ?></span>
                                </a>
                            </li>
                            
                            <!-- Bulk Payment Invoices -->
                            <li <?php if($page_name=="bulkpayment/invoice_list" || $page_name=="bulkpayment/admin_invoices" || $page_name=="bulkpayment/invoice_detail") echo 'class="active-link"'; ?>>
                                <a href="<?=base_url()?>admin/bulkpayment/invoices">
                                    <i class="fa fa-file-text"></i>
                                    <span class="menu-title"><?php echo translate('payment_invoices'); ?></span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php } ?>



<!-- //////////////////////////////////////////// BULK PAYMETN  NAVEBAR CRATING  END /////////////////////////////////////////// -->








                        <?php if ($this->Crud_model->admin_permission('contact_messages')) { ?>
                        <li <?php if(in_array($page_name, ['contact_messages', 'newsletter'])) echo 'class="active-sub active"'; ?>>
                            <a href="#">
                                <i class="fa fa-users"></i>
                                <span class="menu-title"><?php echo translate('messaging')?></span>
                                <i class="arrow"></i>
                            </a>
                            <ul class="collapse">
                                <?php if ($this->Crud_model->admin_permission('contact_messages')) { ?>
                                <li <?php if($page_name=="contact_messages") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/contact_messages"><i class="fa fa-user-o"></i><?php echo translate('contact_messages')?></a>
                                </li>
                                <?php } ?>
                                <li <?php if($page_name=="newsletter") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/newsletter"><i class="fa fa-envelope-o"></i><?php echo translate('newsletter')?></a>
                                </li>
                            </ul>
                        </li>
                        <?php } ?>

                        <?php if ($this->Crud_model->admin_permission('general_settings')) { ?>
                        <li <?php if($page_name=="general_settings") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/general_settings">
                                <i class="fa fa-cog"></i>
                                <span class="menu-title"><?php echo translate('general_settings')?></span>
                            </a>
                        </li>
                        <?php } ?>

                        <?php if ($this->Crud_model->admin_permission('frontend_settings')) { ?>
                        <li <?php if(in_array($page_name, ['header', 'pages', 'footer', 'theme_color_settings'])) echo 'class="active-sub active"'; ?>>
                            <a href="#">
                                <i class="fa fa-desktop"></i>
                                <span class="menu-title"><?php echo translate('frontend_settings')?></span>
                                <i class="arrow"></i>
                            </a>
                            <ul class="collapse">
                                <?php if ($this->Crud_model->admin_permission('choose_theme_color')) { ?>
                                <li <?php if($page_name=="theme_color_settings") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/theme_color_settings"><i class="fa fa-paint-brush"></i><?php echo translate('choose_theme_color')?></a>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('frontend_appearances')) { ?>
                                <li <?php if(in_array($page_name, ['header', 'pages', 'footer'])) echo 'class="active-sub active"'; ?>>
                                    <a href="<?=base_url()?>admin/frontend_appearances"><i class="fa fa-window-restore"></i><?php echo translate('frontend_appearances')?><i class="arrow"></i></a>
                                    <ul class="collapse">
                                        <?php if ($this->Crud_model->admin_permission('header')) { ?>
                                        <li <?php if($page_name=="header") echo 'class="active-link"'; ?>>
                                            <a href="<?=base_url()?>admin/frontend_appearances/header"><i class="fa fa-circle-o"></i><?php echo translate('header')?></a>
                                        </li>
                                        <?php } if ($this->Crud_model->admin_permission('pages')) { ?>
                                        <li <?php if($page_name=="pages") echo 'class="active-link"'; ?>>
                                            <a href="<?=base_url()?>admin/frontend_appearances/pages"><i class="fa fa-circle-o"></i><?php echo translate('pages')?></a>
                                        </li>
                                        <?php } if ($this->Crud_model->admin_permission('footer')) { ?>
                                        <li <?php if($page_name=="footer") echo 'class="active-link"'; ?>>
                                            <a href="<?=base_url()?>admin/frontend_appearances/footer"><i class="fa fa-circle-o"></i><?php echo translate('footer')?></a>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>
                        <?php } ?>

                        <?php if ($this->Crud_model->admin_permission('configuration')) { ?>
                        <li <?php if(in_array($page_name, ['religion', 'caste', 'sub_caste', 'language', 'country', 'state', 'city', 'family_value', 'family_status', 'payments', 'social_media_comments', 'faq', 'email_setup', 'captcha_settings', 'social_media_login', 'google_analytics_settings', 'facebook_chat_settings', 'msg91', 'twilio', 'on_behalf', 'currency_configure', 'currency_set', 'profile_sections'])) echo 'class="active-sub active"'; ?>>
                            <a href="#">
                                <i class="fa fa-cogs"></i>
                                <span class="menu-title"><?php echo translate('configurations')?></span>
                                <i class="arrow"></i>
                            </a>
                            <ul class="collapse">
                                <?php if ($this->Crud_model->admin_permission('member_profile')) { ?>
                                <li <?php if(in_array($page_name, ['religion', 'caste', 'sub_caste', 'language', 'country', 'state', 'city', 'family_value', 'family_status', 'on_behalf'])) echo 'class="active-sub active"'; ?>>
                                    <a href="#"><i class="fa fa-user-plus"></i><?php echo translate('profile_attributes')?><i class="arrow"></i></a>
                                    <ul class="collapse">
                                        <?php if ($this->db->get_where('frontend_settings', ['type' => 'spiritual_and_social_background'])->row()->value == "yes") { ?>
                                        <?php if ($this->Crud_model->admin_permission('religion')) { ?>
                                        <li <?php if($page_name=="religion") echo 'class="active-link"'; ?>>
                                            <a href="<?=base_url()?>admin/religion"><i class="fa fa-circle-o"></i><?php echo translate('religion')?></a>
                                        </li>
                                        <?php } ?>
                                        <?php } if ($this->db->get_where('frontend_settings', ['type' => 'language'])->row()->value == "yes") { ?>
                                        <?php if ($this->Crud_model->admin_permission('language')) { ?>
                                        <li <?php if($page_name=="language") echo 'class="active-link"'; ?>>
                                            <a href="<?=base_url()?>admin/language"><i class="fa fa-circle-o"></i><?php echo translate('language')?></a>
                                        </li>
                                        <?php } ?>
                                        <?php } if ($this->db->get_where('frontend_settings', ['type' => 'present_address'])->row()->value == "yes") { ?>
                                        <?php if ($this->Crud_model->admin_permission('country')) { ?>
                                        <li <?php if($page_name=="country") echo 'class="active-link"'; ?>>
                                            <a href="<?=base_url()?>admin/country"><i class="fa fa-circle-o"></i><?php echo translate('country')?></a>
                                        </li>
                                        <?php } if ($this->Crud_model->admin_permission('state')) { ?>
                                        <li <?php if($page_name=="state") echo 'class="active-link"'; ?>>
                                            <a href="<?=base_url()?>admin/state"><i class="fa fa-circle-o"></i><?php echo translate('state')?></a>
                                        </li>
                                        <?php } if ($this->Crud_model->admin_permission('city')) { ?>
                                        <li <?php if($page_name=="city") echo 'class="active-link"'; ?>>
                                            <a href="<?=base_url()?>admin/city"><i class="fa fa-circle-o"></i><?php echo translate('city')?></a>
                                        </li>
                                        <?php } ?>
                                        <?php } ?>
                                    </ul>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('profile_sections')) { ?>
                                <li <?php if($page_name=="profile_sections") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/profile_sections"><i class="fa fa-address-card-o"></i><?php echo translate('profile_sections')?></a>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('social_media_comments')) { ?>
                                <li <?php if($page_name=="social_media_comments") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/social_media_comments"><i class="fa fa-comments-o"></i><?php echo translate('social_media_comments')?></a>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('payments')) { ?>
                                <li <?php if($page_name=="payments") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/payments"><i class="fa fa-credit-card-alt"></i><?php echo translate('payments')?></a>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('email_setup')) { ?>
                                <li <?php if($page_name=="email_setup") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/email_setup"><i class="fa fa-envelope"></i><?php echo translate('email_setup')?></a>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('currency_settings')) { ?>
                                <li <?php if(in_array($page_name, ['currency_configure', 'currency_set'])) echo 'class="active-sub active"'; ?>>
                                    <a href="<?=base_url()?>admin/"><i class="fa fa-money"></i><?php echo translate('currency_settings')?><i class="arrow"></i></a>
                                    <ul class="collapse">
                                        <li <?php if($page_name=="currency_configure") echo 'class="active-link"'; ?>>
                                            <a href="<?=base_url()?>admin/currency_settings/currency_configure"><i class="fa fa-circle-o"></i><?php echo translate('configure')?></a>
                                        </li>
                                        <li <?php if($page_name=="currency_set") echo 'class="active-link"'; ?>>
                                            <a href="<?=base_url()?>admin/currency_settings/currency_set"><i class="fa fa-circle-o"></i><?php echo translate('all_currencies')?></a>
                                        </li>
                                    </ul>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('captcha_settings')) { ?>
                                <li <?php if($page_name=="captcha_settings") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/captcha_settings"><i class="fa fa-retweet"></i><?php echo translate('captcha_settings')?></a>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('social_media_login_settings')) { ?>
                                <li <?php if($page_name=="social_media_login") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/social_media_login"><i class="fa fa-facebook-square"></i><?php echo translate('social_media_login')?></a>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('google_analytics_settings')) { ?>
                                <li <?php if($page_name=="google_analytics_settings") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/google_analytics_settings"><i class="fa fa-bar-chart"></i><?php echo translate('google_analytics_settings')?></a>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('facebook_chat_settings')) { ?>
                                <li <?php if($page_name=="facebook_chat_settings") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/facebook_chat_settings"><i class="fa fa-facebook-square"></i><?php echo translate('facebook_chat_settings')?></a>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('sms_settings')) { ?>
                                <li <?php if(in_array($page_name, ['twilio', 'msg91'])) echo 'class="active-sub active"'; ?>>
                                    <a href="<?=base_url()?>admin/sms_settings"><i class="fa fa-window-restore"></i><?php echo translate('sms_settings')?><i class="arrow"></i></a>
                                    <ul class="collapse">
                                        <li <?php if($page_name=="twilio") echo 'class="active-link"'; ?>>
                                            <a href="<?=base_url()?>admin/sms_settings/twilio"><i class="fa fa-circle-o"></i><?php echo translate('twilio')?></a>
                                        </li>
                                        <li <?php if($page_name=="msg91") echo 'class="active-link"'; ?>>
                                            <a href="<?=base_url()?>admin/sms_settings/msg91"><i class="fa fa-circle-o"></i><?php echo translate('msg91')?></a>
                                        </li>
                                    </ul>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('faq')) { ?>
                                <li <?php if($page_name=="faq") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/faq"><i class="fa fa-question-circle"></i><?php echo translate('FAQ')?></a>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>
                        <?php } ?>

                        <?php if ($this->Crud_model->admin_permission('send_sms')) { ?>
                        <li <?php if($page_name=="send_sms") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/send_sms">
                                <i class="fa fa-mobile"></i>
                                <span class="menu-title"><?php echo translate('send_SMS')?></span>
                            </a>
                        </li>
                        <?php } ?>

                        <?php if ($this->Crud_model->admin_permission('language')) { ?>
                        <li <?php if($page_name=="manage_language") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/manage_language">
                                <i class="fa fa-language"></i>
                                <span class="menu-title"><?php echo translate('language')?></span>
                            </a>
                        </li>
                        <?php } ?>

                        <?php if ($this->Crud_model->admin_permission('manage_admin')) { ?>
                        <li <?php if($page_name=="manage_admin") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/manage_admin">
                                <i class="fa fa-lock"></i>
                                <span class="menu-title"><?php echo translate('manage_admin_profile')?></span>
                            </a>
                        </li>
                        <?php } ?>

                        <?php if ($this->Crud_model->admin_permission('seo_settings')) { ?>
                        <li <?php if($page_name=="seo_settings") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/seo_settings">
                                <i class="fa fa-search"></i>
                                <span class="menu-title"><?php echo translate('SEO_settings')?></span>
                            </a>
                        </li>
                        <?php } ?>

                        <?php if ($this->Crud_model->admin_permission('staffs_panel')) { ?>
                        <li <?php if(in_array($page_name, ['role', 'admin'])) echo 'class="active-link"'; ?>>
                            <a href="#">
                                <i class="fa fa-user-circle"></i>
                                <span class="menu-title"><?php echo translate('staffs_panel'); ?></span>
                                <i class="fa arrow"></i>
                            </a>
                            <ul class="collapse <?php if(in_array($page_name, ['admin', 'role'])) echo 'in'; ?>">
                                <?php if ($this->Crud_model->admin_permission('all_staffs')) { ?>
                                <li <?php if($page_name=="admin") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/admins/"><i class="fa fa-users"></i><?php echo translate('all_staffs'); ?></a>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('manage_roles')) { ?>
                                <li <?php if($page_name=="role") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/role/"><i class="fa fa-sliders"></i><?php echo translate('manage_roles'); ?></a>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>
                        <?php } ?>

                        <?php if ($this->session->userdata('admin_id') == 1) { ?>
                        <li <?php if($page_name=="backup") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/backup/">
                                <i class="fa fa-shopping-basket"></i>
                                <span class="menu-title"><?php echo translate('backup')?></span>
                            </a>
                        </li>
                        <li <?php if($page_name=="update") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/update/">
                                <i class="fa fa-check-square-o"></i>
                                <span class="menu-title"><?php echo translate('update')?></span>
                            </a>
                        </li>
                        <?php } ?>

                        <?php if ($this->Crud_model->admin_permission('staffs_panel')) { ?>
                        <li <?php if(in_array($page_name, ['role', 'network'])) echo 'class="active-link"'; ?>>
                            <a href="#">
                                <i class="fa fa-user-circle"></i>
                                <span class="menu-title"><?php echo translate('network'); ?></span>
                                <i class="fa arrow"></i>
                            </a>
                            <ul class="collapse <?php if($page_name=="network") echo 'in'; ?>">
                                <?php if ($this->Crud_model->admin_permission('all_staffs')) { ?>
                                <li <?php if($page_name=="network") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/network"><i class="fa fa-user"></i><?php echo translate('profile'); ?></a>
                                </li>
                                <?php } if ($this->Crud_model->admin_permission('manage_roles')) { ?>
                                <li <?php if($page_name=="group") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/network_group"><i class="fa fa-users"></i><?php echo translate('groups'); ?></a>
                                </li>
                                <li <?php if($page_name=="role") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/network_connection"><i class="fa fa-sliders"></i><?php echo translate('connection'); ?></a>
                                </li>
                                <li <?php if($page_name=="role") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/network_testimonials"><i class="fa fa-comments"></i><?php echo translate('testimonials'); ?></a>
                                </li>
                                <li <?php if($page_name=="role") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/network_picture"><i class="fa fa-picture-o"></i><?php echo translate('picture_gallery'); ?></a>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>
                        <li <?php if(in_array($page_name, ['chapter', 'operation'])) echo 'class="active-link"'; ?>>
                            <a href="#">
                                <i class="fa fa-cogs"></i>
                                <span class="menu-title"><?php echo translate('operation'); ?></span>
                                <i class="fa arrow"></i>
                            </a>
                            <ul class="collapse <?php if(in_array($page_name, ['operation', 'chapter'])) echo 'in'; ?>">
                                <li <?php if($page_name=="operation") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/operations"><i class="fa fa-user"></i><?php echo translate('chapter'); ?></a>
                                </li>
                            </ul>
                        </li>
                        <li <?php if(in_array($page_name, ['role', 'report'])) echo 'class="active-link"'; ?>>
                            <a href="#">
                                <i class="fa fa-book"></i>
                                <span class="menu-title"><?php echo translate('report'); ?></span>
                                <i class="fa arrow"></i>
                            </a>
                            <ul class="collapse <?php if(in_array($page_name, ['report', 'chapter'])) echo 'in'; ?>">
                                <li <?php if($page_name=="report") echo 'class="active-link"'; ?>>
                                    <a href="<?=base_url()?>admin/report"><i class="fa fa-user"></i><?php echo translate('chapter'); ?></a>
                                </li>
                            </ul>
                        </li>
                        <?php } ?>

                        <?php if ($this->Crud_model->admin_permission('business_network_shortcut')) { ?>
                        <li <?php if($page_name=="my_bussiness") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/my_business">
                                <i class="fa fa-mobile"></i>
                                <span class="menu-title"><?php echo translate('my_bussiness')?></span>
                            </a>
                        </li>
                        <li <?php if($page_name=="my_network") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/my_network">
                                <i class="fa fa-mobile"></i>
                                <span class="menu-title"><?php echo translate('my_network')?></span>
                            </a>
                        </li>
                        <li <?php if($page_name=="shortcuts") echo 'class="active-link"'; ?>>
                            <a href="<?=base_url()?>admin/shortcuts">
                                <i class="fa fa-mobile"></i>
                                <span class="menu-title"><?php echo translate('shortcuts')?></span>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
        <!-- End menu -->
    </div>
</nav>
<!-- END MAIN NAVIGATION -->