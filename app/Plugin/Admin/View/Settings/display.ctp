<div class="box dark">
    <header>
        <div class="icons"><i class="icon-cog"></i></div>
        <h5><?php echo __('Configure MushRaider');?></h5>
    </header>
    <div class="accordion-body body in collapse">
        <?php echo $this->Form->create('Setting', array('url' => '/admin/settings/display', 'class' => 'span12', 'enctype' => 'multipart/form-data'));?>
            <h3><?php echo __('Theming');?></h3>
            <div class="well well-white">
                <div class="form-group">
                    <?php echo $this->Form->input('Setting.theme.logo', array('type' => 'file', 'label' => __('Logo'), 'class' => 'span5 imageupload'));?>
                    <div id="previewcanvascontainer">
                        <canvas id="previewcanvas" class="w250"></canvas>
                        <?php echo $this->Html->image($this->data['Setting']['theme']['logo'], array('class' => 'currentLogo'));?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo $this->Form->input('Setting.theme.bgimage', array('type' => 'file', 'label' => __('Background Image'), 'class' => 'span5'));?>
                    <?php echo $this->Form->input('Setting.theme.bgnoimage', array('type' => 'checkbox', 'label' => __('no background image')));?>
                    <?php echo $this->Form->input('Setting.theme.bgoriginal', array('type' => 'checkbox', 'label' => __('back to original background')));?>
                </div>
                <div class="form-group">
                    <?php echo $this->Form->input('Setting.theme.bgcolor', array('type' => 'text', 'label' => __('Background Color'), 'class' => 'span1 colorpicker'));?>
                </div>
                <div class="form-group">
                    <?php echo $this->Form->input('Setting.theme.bgrepeat', array('type' => 'select', 'options' => array('repeat' => 'repeat', 'no-repeat' => 'no-repeat', 'repeat-x' => 'repeat-x', 'repeat-y' => 'repeat-y'), 'label' => __('Background Repeat'), 'class' => 'span5'));?>
                </div>
                <div class="form-group">
                    <?php echo $this->Form->input('Setting.css', array('type' => 'textarea', 'label' => __('Custom CSS'), 'class' => 'span5'));?>
                </div>
            </div>

            <h3><?php echo __('Custom links');?></h3>
            <div class="well well-white">
                <blockquote><p class="muted"><?php echo __('Custom links are display in top of the header. They are usefull to add links to your website, forums etc...');?></p></blockquote>
                <?php for($i = 0;$i <= 3;$i++):?>
                    <div class="form-group" style="overflow:hidden">
                        <div class="span3">
                            <?php echo $this->Form->input('Setting.links.'.$i.'.title', array('type' => 'text', 'label' => __('Link title'), 'class' => 'span5z'));?>
                        </div>
                        <div class="span9">
                            <?php echo $this->Form->input('Setting.links.'.$i.'.url', array('type' => 'url', 'label' => __('Url'), 'class' => 'span5'));?>
                        </div>
                    </div>
                <?php endfor;?>
            </div>

            <div class="form-group">
                <?php echo $this->Form->submit(__('Save'), array('class' => 'btn btn-success pull-right'));?>               
            </div>
        <?php echo $this->Form->end();?>
    </div>    
</div>