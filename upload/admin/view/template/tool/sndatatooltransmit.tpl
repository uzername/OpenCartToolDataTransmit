    <?php 
        //template file for sndatatooltransmit. JS AND PHP AND HTML
        echo $header; ?>
    <?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($success) { ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-exclamation-triangle"></i> <?php echo $text_file; ?></h3>
      </div>
      <div class="panel-body">
          <form method="POST" action=<?php echo "\"".$processdataimport."\"" ?> enctype="multipart/form-data">
              <div style="display:table">
                <div style="display:table-row">
                    <div style="display:table-cell; padding-right:10px;"> <input type="radio" name="uploadoption[]" value="pc" checked> <?php echo $text_file_pc; ?> : </div>
                    <div style="display:table-cell"> <input type="file" name="xml" accept="text/xml" /> </div>
                </div>    
                <div style="display:table-row">  
                    <div style="display:table-cell; padding-right:10px;"> <input type="radio" name="uploadoption[]" value="server"> <?php echo $text_file_server; ?> : </div> 
                    <div style="display:table-cell"> <input type="text" name="serveraddress" /> </div>
                </div>    
                <div style="display:table-row">  
                    <div style="display:table-cell; padding-right:10px;"> <input type="radio" name="uploadoption[]" value="other"> <?php echo $text_file_other; ?> : </div> 
                    <div style="display:table-cell"> <input type="url" name="otheraddress" /> </div>
                </div>
              </div>
              <div style="display: none"> <input type="checkbox" name="cleanupfileoption"> <?php echo $text_file_remove_afteruse ?> </div>
              <input type="submit" />
          </form>
      </div>
          <div class="panel-heading">
              <?php echo $text_file_resultscapt; ?>
          </div>
      <div class="panel-body" id="sn-import-results">  
          <?php
          if (isset($append_to_log) && ($append_to_log == TRUE)) {
              foreach ($log_lines as $single_log_line) {
                  echo "<div>".$single_log_line."</div>"; 
              }
          }
          ?>
      </div>
      
    </div>
  </div>
</div>
<?php echo $footer; ?>