<p>
    <label for="<?php echo $this->widget->get_field_id('title')?>"><?php _e('Title', TBS_LANG_CODE)?>:</label>
    <?php 
        echo htmlTbs::text($this->widget->get_field_name('title'), array(
            'attrs' => 'id="'. $this->widget->get_field_id('title'). '"', 
            'value' => (isset($this->data['title']) ? $this->data['title'] : '')));
    ?><br />
	<label for="<?php echo $this->widget->get_field_id('display_type')?>"><?php _e('Display Type', TBS_LANG_CODE)?>:</label>
    <?php 
        echo htmlTbs::selectbox($this->widget->get_field_name('display_type'), array(
            'attrs' => 'id="'. $this->widget->get_field_id('display_type'). '"', 
			'options' => array(
				'links_list' => __('Simple Links', TBS_LANG_CODE),
				'buttons_list' => __('Buttons', TBS_LANG_CODE),
				'selectbox' => __('Select Box', TBS_LANG_CODE),
				'selectbox_cust' => __('Customized Select Box', TBS_LANG_CODE),
			),
            'value' => (isset($this->data['display_type']) ? $this->data['display_type'] : ''),
		));
    ?><br />
	<label for="<?php echo $this->widget->get_field_id('show_flag')?>"><?php _e('Show Flag Icon', TBS_LANG_CODE)?>:</label>
    <?php 
        echo htmlTbs::checkbox($this->widget->get_field_name('show_flag'), array(
            'attrs' => 'id="'. $this->widget->get_field_id('show_flag'). '"', 
            'checked' => (isset($this->data['show_flag']) ? $this->data['show_flag'] : false),
		));
    ?><br />
	<label title="<?php _e('This will show Only flag icon, without Language Label. Will work only for Simple Links and Buttons Display Type.', TBS_LANG_CODE)?>" for="<?php echo $this->widget->get_field_id('show_only_flag')?>"><?php _e('Show Only Flag Icon', TBS_LANG_CODE)?>:</label>
	
    <?php 
        echo htmlTbs::checkbox($this->widget->get_field_name('show_only_flag'), array(
            'attrs' => 'id="'. $this->widget->get_field_id('show_only_flag'). '"', 
            'checked' => (isset($this->data['show_only_flag']) ? $this->data['show_only_flag'] : false),
		));
    ?>
	<br />
	<label for="<?php echo $this->widget->get_field_id('flag_size')?>"><?php _e('Flag Icons Size', TBS_LANG_CODE)?>:</label>
    <?php 
        echo htmlTbs::selectbox($this->widget->get_field_name('flag_size'), array(
            'attrs' => 'id="'. $this->widget->get_field_id('flag_size'). '"', 
			'options' => array(
				16 => '16px',
				24 => '24px',
				32 => '32px',
				48 => '48px',
			),
            'value' => (isset($this->data['flag_size']) ? $this->data['flag_size'] : 16),
		));
    ?><br />
</p>