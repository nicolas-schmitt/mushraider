<header>
    <h1><i class="icon-gears"></i> <?php echo __('My settings');?></h1>
</header>

<div class="row">
    <div class="span3">
        <?php echo $this->element('account_menu');?>
    </div>
    <div class="span8">
        <?php echo $this->Form->create('User', array('url' => '/account/settings', 'class' => ''));?>
            <div class="row-fluid">
                <h3><?php echo __('Email notifications');?></h3>
                <?php echo $this->Form->input('User.notifications_cancel', array('type' => 'checkbox', 'label' => __('Receive email notifications when an event is cancelled')));?>
                <?php echo $this->Form->input('User.notifications_new', array('type' => 'checkbox', 'label' => __('Receive email notifications when an event is created')));?>
                <?php echo $this->Form->input('User.notifications_validate', array('type' => 'checkbox', 'label' => __('Receive email notifications when I\'m validated to an event')));?>
            </div> 
            <div class="row-fluid">
                <div class="span2">
                    <?php echo $this->Form->submit(__('Save'), array('class' => 'btn btn-warning'));?>
                </div>
            </div>
        <?php echo $this->Form->end();?>
    </div>
</div>