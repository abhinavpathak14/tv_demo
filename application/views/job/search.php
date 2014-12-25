<?php
echo form_open('job/index', array('id' => 'search_form', 'name' => 'search_form'));
?>
<div>Skill: <?php echo form_input('skill', set_value('skill'), 'placeholder="Enter skill.." id="skill"') ?></div>
<div>Location: <?php echo form_input('location', set_value('location'), 'placeholder="Enter location.." id="location"') ?></div>
<div>Pagination: <?php echo form_input('pagination', set_value('pagination'), 'placeholder="Enter pagination.." id="pagination"') ?></div>
<?php echo form_submit('submit_btn', 'Search', 'id="submit_btn" style="display:none"') ?>
<?php echo form_close(); ?>