<style>
    #table-checklist > thead > tr > th {
        font-size: 20px;
        padding: 5px;
    }
    tr.task_row_need_update {
        border-right: 4px solid orange;
    }
</style>

<div class='row my-4'>
    <div class='col-6'>
        <a class="btn btn-secondary btnClose float-left" data-dismiss="modal"><i class="far fa-window-close"></i> Close</a>
    </div>
    <div class='col-6'>
<!--        <a class="btn btn-success float-right" data-multi='yes'>Save<i class="far fa-square"></i></a>-->
    </div>
</div>
<h3 class='my-4'><?php echo $product_name?> Checklist</h3>
<form id="frmProductChecklist">
    <table id='table-checklist' class='table'>
        <thead>
            <th></th>
            <th width="30%">Task</th>
            <th>Who</th>
            <th>When</th>
            <th>Notes</th>
        </thead>
        <tbody>
            <?php echo $table_body_rows?>
        </tbody>
    </table>
<!--    --><?php//=$table_html;?>
</form>
<script>
    function task_change_event(task_id){
        $("tr[name='task_row_"+task_id+"']").addClass('task_row_need_update');
    }
</script>
