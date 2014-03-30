<?php echo $header; ?>
<!-- SEO Mage -->
<style type="text/css">
  .status-on { width:56px;height:24px;background:url(view/image/seomage_on.png) top left no-repeat;cursor:pointer;margin-top:5px; }
  .status-off { width:56px;height:24px;background:url(view/image/seomage_off.png) top left no-repeat;cursor:pointer;margin-top:5px; }
  .sm_code { background-color:#eee; }
</style>

<div id="content">

  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  
  <div class="box">

    <div class="heading">
      <h1><img src="view/image/module.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
    </div>

    <div class="content">

      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">

        <table class="form">
          <tr>
            <td><?php echo $entry_status; ?></td>
            <td><?php if($seomage_status == 1) { echo '<div class="status status-on" title="1" rel="seomage_status"></div>'; } else { echo '<div class="status status-off" title="0" rel="seomage_status"></div>'; } ?><input name="seomage_status" value="<?php echo $seomage_status; ?>" id="seomage_status" type="hidden" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_debug; ?></td>
            <td><?php if($seomage_debug == 1) { echo '<div class="status status-on" title="1" rel="seomage_debug"></div>'; } else { echo '<div class="status status-off" title="0" rel="seomage_debug"></div>'; } ?><input name="seomage_debug" value="<?php echo $seomage_debug; ?>" id="seomage_debug" type="hidden" />
            </td>
          </tr>
          <tr>
            <td colspan="2"><?php echo $text_version; ?></span><br><?php echo $text_version_hint; ?></td></tr>
          <tr>
            <td><?php echo $entry_checkupdate; ?></td>
            <td><?php if($seomage_checkupdate == 1) { echo '<div class="status status-on" title="1" rel="seomage_checkupdate"></div>'; } else { echo '<div class="status status-off" title="0" rel="seomage_checkupdate"></div>'; } ?><input name="seomage_checkupdate" value="<?php echo $seomage_checkupdate; ?>" id="seomage_checkupdate" type="hidden" />
            </td>
          </tr>
        </table>
        <div id="seomage_tabs" class="htabs clearfix">
          <a href="#tab_edit"><?php echo $tab_edit; ?></a>
          <a href="#tab_generation"><?php echo $tab_generation; ?></a>
          <a href="#tab_log"><?php echo $tab_log; ?></a>
          <a href="#tab_help"><?php echo $tab_help; ?></a>
        </div>

        <div id="tab_edit" class="divtab">
          <p><?php echo $tab_edit_hint; ?></p>
          <table id="module" class="list">
            <thead>
              <tr>
                <td class="left"><?php echo $entry_route; ?></td>
                <td class="left"><?php echo $entry_keyword; ?></td>
                <td></td>
              </tr>
            </thead>
            <?php $module_row = 0; ?>
            <?php foreach ($keywords as $keyword) { ?>
            <tbody id="module-row<?php echo $module_row; ?>">
              <tr>
                <td class="left">
                  <input type="text" size="50" name="keywords[<?php echo $module_row; ?>][query]" value="<?php echo $keyword['query']; ?>" size="50" />
                </td>
                <td class="left">
                  <input type="text" name="keywords[<?php echo $module_row; ?>][keyword]" value="<?php echo $keyword['keyword']; ?>" size="50" />
                </td>
                <td class="left"><a onclick="$('#module-row<?php echo $module_row; ?>').remove();" class="button"><?php echo $button_remove; ?></a></td>
              </tr>
            </tbody>
            <?php $module_row++; ?>
            <?php } ?>
            <tfoot>
              <tr>
                <td colspan="2"></td>
                <td class="left"><a onclick="addModule();" class="button"><?php echo $button_add_keyword; ?></a></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div id="tab_generation" class="divtab">
          <p><?php echo $tab_generation_hint; ?></p>
          

          <div class="vtabs">
            <a href="#gen_category"><?php echo $text_categories; ?></a>
            <a href="#gen_product"><?php echo $text_products; ?></a>
            <a href="#gen_manufacturer"><?php echo $text_manufacturers; ?></a>
            <a href="#gen_information"><?php echo $text_informations; ?></a>
          </div>

          <div class="vtabs-content">

            <div id="gen_category">
              <div class="message"></div>
              <h3><?php echo $text_gen_categories; ?></h3>
              <table class="form">
                <tbody>
                  <tr>
                    <td><?php echo $text_language; ?></td>
                    <td>
                      <select class="gen_language">
                        <?
                        foreach ($languages as $key => $value) {
                          echo '<option value="'.$value['language_id'].'">'.$value['name'].'</option>';
                        }
                        ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $text_template; ?></td>
                    <td>
                      <select class="gen_method">
                        <option value="{name}">{name}</option>
                        <option value="manual"><?php echo $text_custom_template; ?></option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $text_custom_template; ?>:</td>
                    <td>
                      <input type="text" class="gen_template" size="100" />
                      <br>
                      <p><?php echo $text_available_masks; ?>: <?php echo $text_mask_id; ?>, <?php echo $text_mask_name; ?></p>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $text_lowercase; ?></td>
                    <td>
                      <input class="gen_register_to_low" type="checkbox" checked="checked" />
                      <p><?php echo $text_lowercase_hint; ?></p>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $text_report; ?></td>
                    <td>
                      <select class="gen_report">
                        <option value="full"><?php echo $text_report_full; ?></option>
                        <option value="error"><?php echo $text_report_error; ?></option>
                      </select>
                    </td>
                  </tr>
                </tbody>
              </table>
              <a onclick="generateLinks('gen_category');" class="button"><?php echo $button_generate; ?></a> <span class="gen_loading"></span>
              <br>
              <div class="outreport"></div>
            </div>

            <div id="gen_product">
              <div class="message"></div>
              <h3><?php echo $text_gen_products; ?></h3>
              <table class="form">
                <tbody>
                  <tr>
                    <td><?php echo $text_language; ?></td>
                    <td>
                      <select class="gen_language">
                        <?
                        foreach ($languages as $key => $value) {
                          echo '<option value="'.$value['language_id'].'">'.$value['name'].'</option>';
                        }
                        ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $text_template; ?></td>
                    <td>
                      <select class="gen_method">
                        <option value="{name}">{name}</option>
                        <option value="{name}_{model}">{name}_{model}</option>
                        <option value="{manufacturer}_{name}">{manufacturer}_{name}</option>
                        <option value="{manufacturer}_{name}_{model}">{manufacturer}_{name}_{model}</option>
                        <option value="manual"><?php echo $text_custom_template; ?></option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $text_custom_template; ?>:</td>
                    <td>
                      <input type="text" class="gen_template" size="100" />
                      <br>
                      <p><?php echo $text_available_masks; ?>: <?php echo $text_mask_id; ?>, <?php echo $text_mask_name; ?>, <?php echo $text_mask_category; ?>, <?php echo $text_mask_model; ?>, <?php echo $text_mask_sku; ?>, <?php echo $text_mask_manufacturer; ?>, </p>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $text_lowercase; ?></td>
                    <td>
                      <input class="gen_register_to_low" type="checkbox" checked="checked" />
                      <p><?php echo $text_lowercase_hint; ?></p>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $text_report; ?></td>
                    <td>
                      <select class="gen_report">
                        <option value="full"><?php echo $text_report_full; ?></option>
                        <option value="error"><?php echo $text_report_error; ?></option>
                      </select>
                    </td>
                  </tr>
                </tbody>
              </table>
              <a onclick="generateLinks('gen_product');" class="button"><?php echo $button_generate; ?></a> <span class="gen_loading"></span>
              <br>
              <div class="outreport"></div>
            </div>

            <div id="gen_manufacturer">
              <div class="message"></div>
              <h3><?php echo $text_gen_manufacturers; ?></h3>
              <table class="form">
                <tbody>
                  <tr>
                    <td><?php echo $text_template; ?></td>
                    <td>
                      <select class="gen_method">
                        <option value="{name}">{name}</option>
                        <option value="manual"><?php echo $text_custom_template; ?></option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $text_custom_template; ?>:</td>
                    <td>
                      <input type="text" class="gen_template" size="100" />
                      <br>
                      <p><?php echo $text_available_masks; ?>: <?php echo $text_mask_id; ?>, <?php echo $text_mask_name; ?></p>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $text_lowercase; ?></td>
                    <td>
                      <input class="gen_register_to_low" type="checkbox" checked="checked" />
                      <p><?php echo $text_lowercase_hint; ?></p>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $text_report; ?></td>
                    <td>
                      <select class="gen_report">
                        <option value="full"><?php echo $text_report_full; ?></option>
                        <option value="error"><?php echo $text_report_error; ?></option>
                      </select>
                    </td>
                  </tr>
                </tbody>
              </table>

              <input type="hidden" class="gen_language" value="none" />

              <a onclick="generateLinks('gen_manufacturer');" class="button"><?php echo $button_generate; ?></a> <span class="gen_loading"></span>
              <br>
              <div class="outreport"></div>
            </div>

            <div id="gen_information">
              <div class="message"></div>
              <h3><?php echo $text_gen_informations; ?></h3>
              <table class="form">
                <tbody>
                  <tr>
                    <td><?php echo $text_language; ?></td>
                    <td>
                      <select class="gen_language">
                        <?
                        foreach ($languages as $key => $value) {
                          echo '<option value="'.$value['language_id'].'">'.$value['name'].'</option>';
                        }
                        ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $text_template; ?></td>
                    <td>
                      <select class="gen_method">
                        <option value="{name}">{name}</option>
                        <option value="manual"><?php echo $text_custom_template; ?></option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $text_custom_template; ?>:</td>
                    <td>
                      <input type="text" class="gen_template" size="100" />
                      <br>
                      <p><?php echo $text_available_masks; ?>: <?php echo $text_mask_id; ?>, <?php echo $text_mask_name; ?></p>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $text_lowercase; ?></td>
                    <td>
                      <input class="gen_register_to_low" type="checkbox" checked="checked" />
                      <p><?php echo $text_lowercase_hint; ?></p>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $text_report; ?></td>
                    <td>
                      <select class="gen_report">
                        <option value="full"><?php echo $text_report_full; ?></option>
                        <option value="error"><?php echo $text_report_error; ?></option>
                      </select>
                    </td>
                  </tr>
                </tbody>
              </table>
              <a onclick="generateLinks('gen_information');" class="button"><?php echo $button_generate; ?></a> <span class="gen_loading"></span>
              <br>
              <div class="outreport"></div>
            </div>

          </div>
        </div>

        <div id="tab_log" class="divtab">
          <p><?php echo $tab_log_hint; ?></p>
          <div id="table_log">
            <ul>
              <?php $log_row = 0; ?>
              <?php foreach ($logs as $log) { ?>
              <?php echo '<li><a target="_blank" href="'.$log['message'].'&token='.$token.'">'.$log['message'].'</a></li>'; ?>
              <?php $log_row++; ?>
              <?php } ?>
            </ul>
          </div>

          <a onclick="clearLog();" class="button"><?php echo $text_clear_log; ?></a> <span class="gen_loading"></span>
        </div>

        <div id="tab_help" class="divtab">
          <p><?php echo $text_help; ?></p>
        </div>

      </form>


    </div>
  </div>
</div>

<script type="text/javascript"><!--
  var module_row = <?php echo $module_row; ?>;

  function addModule() {	
   html  = '<tbody id="module-row' + module_row + '">';
   html += '  <tr>';
   html += '    <td class="left">';
   html += '      <input type="text" name="keywords[' + module_row + '][query]" size="50" />';
   html += '    </td>';
   html += '    <td class="left">';
   html += '      <input type="text" name="keywords[' + module_row + '][keyword]" value="" size="50" />';
   html += '    </td>'; 
   html += '    <td class="left"><a onclick="$(\'#module-row' + module_row + '\').remove();" class="button"><?php echo $button_remove; ?></a></td>';
   html += '  </tr>';
   html += '</tbody>';

   $('#module tfoot').before(html);

   module_row++;
 }
 //--></script> 
 <?php echo $footer; ?>

 <script type="text/javascript">
  jQuery(document).ready(function() {
    $('#seomage_tabs a').tabs();
    $('#tab_generation .vtabs a').tabs();

    $(".status").click(function () {
      var styl = $(this).attr("rel");
      var co = $(this).attr("title");

      if(co == 1) {
        $(this).removeClass('status-on');
        $(this).addClass('status-off');
        $(this).attr("title", "0");
        $("#"+styl+"").val(0);
      }
      if(co == 0) {
        $(this).addClass('status-on');
        $(this).removeClass('status-off');
        $(this).attr("title", "1");
        $("#"+styl+"").val(1);
      }
    });
  }); 


function clearLog() {
  $('.gen_loading').html('<img src="view/image/loading.gif" />');

  jQuery.ajax({
    type: 'post',
    url: 'index.php?route=module/seomage/clearlog&token=<?php echo $token; ?>',
    dataType: 'json',
    success: function(e) {
      $('#table_log').html('');
      $('.gen_loading').html('');
    },
    error: function(e) {
      console.log('AJAX error');
      console.log(e);
      $('.gen_loading').html('');
    }
  });

}

var inajax = false;
function generateLinks(id) {
  if (inajax) { return false; }

  inajax = true;
  $('.gen_loading').html('<img src="view/image/loading.gif" />');

  gendata = {
    'id' : id,
    'language' : jQuery('#'+id+' .gen_language').val(),
    'method' : jQuery('#'+id+' .gen_method').val(),
    'template' : jQuery('#'+id+' .gen_template').val(),
    'report' : jQuery('#'+id+' .gen_report').val(),
    'register_to_low' : jQuery('#'+id+' .gen_register_to_low').is(':checked')
  };

  $('#'+id+' .outreport').html('');

  jQuery.ajax({
    type: 'post',
    url: 'index.php?route=module/seomage/generation&token=<?php echo $token; ?>',
    data: gendata,
    dataType: 'json',
    success: function(e) {
      if (e.success) {
        $('#'+id+' .message').html('<div class="success">'+e.success+'</div>');
      }
      if (e.fail) {
        $('#'+id+' .message').html('<div class="attention">'+e.fail+'</div>');
      }
      var report = '<h3><?php echo $text_report; ?></h3>' + e.report;
      if (e.error_count == 0) { report += '<h3><?php echo $text_no_error; ?></h3>'; }
      $('#'+id+' .outreport').html(report);


      inajax = false;
      $('.gen_loading').html('');
    },
    error: function(e) {
      $('#'+id+' .message').html('<div class="warning"><?php echo $text_ajax_error; ?></div>');
      inajax = false;
      $('.gen_loading').html('');
    }
  });
}
</script>


