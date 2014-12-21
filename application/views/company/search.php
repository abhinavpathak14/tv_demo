<?php
echo form_open('company/search', array('id' => 'search_form', 'name' => 'search_form'));
?>
<div>
    Search: <?php echo form_input('search_txt', set_value('search_txt'), 'placeholder="Enter Company name.." id="search_txt"') ?>
    <?php echo form_submit('submit_btn', 'Search', 'id="submit_btn" style="display:none"') ?>
    <div class="info">Press enter to search after enter search text in box.</div>
</div>
<table cellspacing="0" cellpadding="0" width="100%" border="1">
    <thead>
        <tr>
            <th>Company Name</th>
            <th>Company Details</th>
        </tr>
    </thead>
    <tbody>
        <?php
            if(isset($search_msg) && !empty($search_msg)) {
        ?>
        <tr><td colspan="2"><i><?php echo $search_msg ?></i></td></tr>
        <?php
            } else {
                foreach ($companies->result_array() as $row) {
        ?>
        <tr>
            <td><?php echo $row['company_name'] ?></td>
            <td><?php echo $row['description'] ?></td>
        </tr>
        <?php
                }
            }
        ?>
    </tbody>
</table>
<?php
echo form_close();
?>