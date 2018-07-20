<?php /* Template Name: My order */ ?>
<?php

global $wpdb, $current_user;

if(($_SERVER["REQUEST_METHOD"] == "POST") && isset($_POST['post_id'])){// สร้าง order
  $arrProducts = tamzang_get_all_products_in_cart($current_user->ID, $_POST['post_id']);
  if(!empty($arrProducts))
  {
    $current_date = date("Y-m-d H:i:s");
    $wpdb->query($wpdb->prepare("INSERT INTO orders SET wp_user_id = %d, post_id = %d, order_date = %s, total_amt = %d, status = %d ",
      array($current_user->ID, $_POST['post_id'], $current_date, 0, 1)));
    $order_id = $wpdb->insert_id;

    $sum = 0;

    foreach ($arrProducts as $product) {
      $sum += $product->price*$product->qty;
      $wpdb->query(
        $wpdb->prepare(
          "INSERT INTO order_items SET order_id = %d, product_id = %d, product_name = %s, product_img = %s, qty = %d, price = %d ",
          array($order_id, $product->product_id, $product->name, $product->featured_image, $product->qty, $product->price)
        )
      );

      $wpdb->query(
          $wpdb->prepare(
              "DELETE FROM shopping_cart WHERE product_id = %d AND wp_user_id =%d",
              array($product->product_id, $current_user->ID)
          )
      );

    }// end foreach

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE orders SET total_amt = %d where id =%d",
            array($sum, $order_id)
        )
    );
  }// end if !empty

}// end สร้าง order


$uploads = wp_upload_dir();

get_header(); ?>

<script>
jQuery(document).ready(function($){

  function after_upload(element, data)
  {
    if(data.success)
    {
      ui_single_update_status(element, 'อัพโหลดเรียบร้อย', 'success');
      $('#slip_pic_'+data.data.order_id).attr('src', data.data.image);
      $('#slip_pic_'+data.data.order_id).css("display", "inline");
    }else
    {
      ui_single_update_status(element, 'อัพโหลดไม่ถูกต้อง', 'danger');
    }
  }

  function ui_single_update_active(element, active)
  {
    element.find('div.progress').toggleClass('d-none', !active);
    element.find('input[type="text"]').toggleClass('d-none', active);

    element.find('input[type="file"]').prop('disabled', active);
    element.find('.btn').toggleClass('disabled', active);

    element.find('.btn i').toggleClass('fa-circle-o-notch fa-spin', active);
    element.find('.btn i').toggleClass('fa-folder-o', !active);
  }

  function ui_single_update_progress(element, percent, active)
  {
    active = (typeof active === 'undefined' ? true : active);

    var bar = element.find('div.progress-bar');

    bar.width(percent + '%').attr('aria-valuenow', percent);
    bar.toggleClass('progress-bar-striped progress-bar-animated', active);

    if (percent === 0){
      bar.html('');
    } else {
      bar.html(percent + '%');
    }
  }

  function ui_single_update_status(element, message, color)
  {
    color = (typeof color === 'undefined' ? 'muted' : color);

    element.find('small.status').prop('class','status text-' + color).html(message);
  }

  $('#drag-and-drop-zone').dmUploader({ //
    url: ajaxurl+'?action=add_transfer_slip_picture',
    maxFileSize: 3000000, // 3 Megs max
    multiple: false,
    allowedTypes: 'image/*',
    extFilter: ['jpg','jpeg','png'],
    dataType: 'json',
    extraData: function() {
     return {
       "order_id": $('#order_id').val(),
       "nonce": $('#nonce').val()
     };
    },
    onDragEnter: function(){
      // Happens when dragging something over the DnD area
      this.addClass('active');
    },
    onDragLeave: function(){
      // Happens when dragging something OUT of the DnD area
      this.removeClass('active');
    },
    onInit: function(){
      // Plugin is ready to use
      //this.find('input[type="text"]').val('');
    },
    onComplete: function(){
      // All files in the queue are processed (success or error)

    },
    onNewFile: function(id, file){
      // When a new file is added using the file selector or the DnD area


      if (typeof FileReader !== "undefined"){
        var reader = new FileReader();
        var img = this.find('img');

        reader.onload = function (e) {
          img.attr('src', e.target.result);
        }
        reader.readAsDataURL(file);
        img.css("display", "inline");
      }
    },
    onBeforeUpload: function(id){
      // about tho start uploading a file

      ui_single_update_progress(this, 0, true);
      //ui_single_update_active(this, true);

      ui_single_update_status(this, 'Uploading...');
    },
    onUploadProgress: function(id, percent){
      // Updating file progress
      ui_single_update_progress(this, percent);
    },
    onUploadSuccess: function(id, data){
      //var response = JSON.stringify(data);

      // A file was successfully uploaded

      //ui_single_update_active(this, false);

      // You should probably do something with the response data, we just show it
      //this.find('input[type="text"]').val(response);
      after_upload(this, data);

    },
    onUploadError: function(id, xhr, status, message){
      // Happens when an upload error happens
      //ui_single_update_active(this, false);
      ui_single_update_status(this, 'Error: ' + message, 'danger');
    },
    onFallbackMode: function(){
      // When the browser doesn't support this plugin :(

    },
    onFileSizeError: function(file){
      ui_single_update_status(this, 'ขนาดรูปภาพเกิน 3MB', 'danger');

    },
    onFileTypeError: function(file){
      ui_single_update_status(this, 'ไฟล์ที่อัพโหลดต้องเป็นไฟล์รูปภาพเท่านั้น', 'danger');

    },
    onFileExtError: function(file){
      ui_single_update_status(this, 'File extension not allowed', 'danger');

    }
  });

  $('#add-transfer-slip').on('show.bs.modal', function(e) {
      var data = $(e.relatedTarget).data();
      $('.title', this).text(data.id);
      //$('.btn-default', this).data('orderId', data.orderId);
      $('#nonce', this).val(data.nonce);
      $('#order_id', this).val(data.id);
      //console.log($(this).find('.title').text());
      var bar = $('#drag-and-drop-zone').find('div.progress-bar');
      bar.width(0 + '%').attr('aria-valuenow', 0);
      bar.html(0 + '%');

      $('#drag-and-drop-zone', this).find('small.status').html('');
      $('img', this).css("display", "none");
  });







  jQuery(document).on("change", ".order-status", function(){
    var order_status = $(this).val();
    var order_id = $(this).data('id');
    var nonce = $(this).data('nonce');
    console.log(order_status+"--"+order_id+"--"+nonce);


    $('.wrapper-loading').toggleClass('cart-loading');
    var send_data = 'action=update_order_status&id='+order_id+'&nonce='+nonce+'&status='+order_status;

    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: send_data,
      success: function(msg){
            console.log( "Updated status callback: " + JSON.stringify(msg) );

            $( "#status_"+order_id ).load( ajaxurl+"?action=load_order_status&order_status="+order_status, function( response, status, xhr ) {
              if ( status == "error" ) {
                var msg = "Sorry but there was an error: ";
                $( "#status_"+order_id ).html( msg + xhr.status + " " + xhr.statusText );
              }
              console.log( "load_order_status: " + status );
              $('.wrapper-loading').toggleClass('cart-loading');
            });


            //$('.wrapper-loading').toggleClass('cart-loading');

      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
         console.log(textStatus);
         $('.wrapper-loading').toggleClass('cart-loading');
      }
    });

  });


  $('#confirm-delete').on('click', '.btn-ok', function(e) {

    var order_id = $(this).data('id');
    var nonce = $(this).data('nonce');
    console.log( "ยกเลิก order: " + order_id );



    var send_data = 'action=update_order_status&id='+order_id+'&nonce='+nonce+'&status='+99;
    $.ajax({
        type: "POST",
        url: ajaxurl,
        data: send_data,
        success: function(msg){
              console.log( "Order cancel: " + JSON.stringify(msg) );
              $( "#panel_"+order_id ).removeClass('panel-default').addClass('panel-danger');
              $( "#panel_"+order_id ).find(".panel-footer").remove();
              $( "#status_"+order_id ).html('<div class="order-row" style="text-align:center;"><h1>ยกเลิก</h1></div>');
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
           console.log(textStatus);

        }
      });

      $('#confirm-delete').modal('toggle');

    });

    $('#confirm-delete').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        $('.title', this).text(data.id);
        $('.btn-ok', this).data('id', data.id);
        $('.btn-ok', this).data('nonce', data.nonce);
        //console.log(data);
    });


    $('[id="flip"]').click(function(){
    	var id = $(this).data('id');
        $("#toggle_pic_"+id).slideToggle("slow");
    });


});
</script>

<div id="geodir_wrapper" class="geodir-single">
  <?php //geodir_breadcrumb();?>
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="" role="main" style="width: 100%">

      <article role="article">
        <header class="article-header">
          <h1 class="page-title entry-title" itemprop="headline">
            <?php the_title(); ?>
          </h1>
          <?php /*<p class="byline vcard"> <?php printf( __( 'Posted <time class="updated" datetime="%1$s" >%2$s</time> by <span class="author">%3$s</span>', GEODIRECTORY_FRAMEWORK ), get_the_time('c'), get_the_time(get_option('date_format')), get_the_author_link( get_the_author_meta( 'ID' ) )); ?> </p> */?>
        </header>
        <?php // end article header ?>
        <section class="entry-content cf" itemprop="articleBody">


          <div class="modal fade" id="add-transfer-slip" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h4 class="modal-title" id="myModalLabel">ยืนยันการสั่งซื้อสินค้า</h4>
                      </div>
                      <div class="modal-body">
                          <p>กรุณาอัพโหลดรูปภาพหลักฐานการโอนเงินของใบสั่งซื้อสินค้า #<b><i class="title"></i></b></p>

                          <form class="mb-3 dm-uploader" id="drag-and-drop-zone">
                            <div class="form-row">
                              <div class="col-md-10 col-sm-12">
                                <div class="from-group mb-2">
                                  <div class="progress mb-2 d-none">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                      role="progressbar"
                                      style="width: 0%;"
                                      aria-valuenow="0" aria-valuemin="0" aria-valuemax="0">
                                      0%
                                    </div>
                                  </div>

                                </div>
                                <div class="form-group">
                                  <label for="file-upload" class="btn btn-primary">
                                      <i class="fa fa-cloud-upload"></i> กรุณาเลือกไฟล์
                                  </label>
                                  <input id="file-upload" type="file" style="display:none;"/>
                                  <small class="status text-muted">Select a file or drag it over this area..</small>
                                </div>
                              </div>
                              <div class="col-md-2  d-md-block  d-sm-none">
                                <img src="" >
                              </div>
                            </div>
                            <input type="hidden" id="order_id" value="" />
                            <input type="hidden" id="nonce" value="" />
                          </form>

                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
                      </div>
                  </div>
              </div>
          </div>




          <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h4 class="modal-title" id="myModalLabel">ยืนยันยกเลิกคำสั่งซื้อ</h4>
                      </div>
                      <div class="modal-body">
                          <p>คุณกำลังจะยกเลิกคำสั่งซื้อรหัส <b><i class="title"></i></b></p>
                          <p>คุณต้องการดำเนินการต่อหรือไม่?</p>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
                          <button type="button" class="btn btn-danger btn-ok">ตกลง</button>
                      </div>
                  </div>
              </div>
          </div>

          <?php

          $arrOrders  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM orders where wp_user_id =%d ORDER BY id DESC ",
                array($current_user->ID)
            )
          );

          foreach ($arrOrders as $order) {
            set_query_var( 'order_status', $order->status );
		      ?>

          <div class="panel <?php echo ($order->status == 99 ? 'panel-danger' : 'panel-default'); ?>" id="panel_<?php echo $order->id; ?>">
            <div class="panel-heading">Order id: #<?php echo $order->id; ?> ร้าน: <a href="<?php echo get_page_link($order->post_id); ?>"><?php echo get_the_title($order->post_id); ?></a>
            </div>
            <div class="panel-body">

              <?php
              $OrderItems  = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM order_items where order_id =%d ",
                    array($order->id)
                )
              );
              foreach ($OrderItems as $product) {

              ?>
                <div class="order-row">
                  <div class="order-col-2">
                    <img style="width:72px;height:72px;" src="<?php echo $uploads['baseurl'].$product->product_img; ?>">
                  </div>
                  <div class="order-col-4">
                    <h4 class="product-name"><strong><?php echo $product->product_name; ?></strong></h4><h4><small><?php //echo $product->short_desc; ?></small></h4>
                  </div>
                  <div class="order-col-6">
                    <div class="order-col-6" style="text-align:right;">
                      <strong><?php echo $product->price; ?> <span class="text-muted">x</span> <?php echo $product->qty; ?></strong>
                    </div>
                    <div class="order-col-2">
                      <strong>รวม</strong>
                    </div>
                    <div class="order-col-4">
                      <strong><?php echo $product->price*$product->qty; ?> บาท</strong>
                    </div>
                  </div>
                </div>
                <div class="order-clear"></div>
                <hr>

              <?php

              }
              ?>

              <div class="order-row">
                <div class="order-col-9" style="text-align:right;">
                  <h4>ทั้งหมด</h4>
              	</div>
                <div class="order-col-3">
                  <h4><strong><?php echo $order->total_amt; ?></strong> บาท</h4>
              	</div>
              </div>
              <div class="order-clear"></div>
              <hr>


              <div class="order-row">
                <div class="wrapper-loading" id="status_<?php echo $order->id; ?>">
                  <?php get_template_part( 'ajax-order-status' ); ?>
                </div>
              </div>

            </div>

            <?php if($order->status != 99){ ?>
              <div class="panel-footer">
                <div class="order-row">
                  <div class="order-col-4" style="text-align:left;min-height:1px;">
                    <?php if($order->status == 1){ ?>
                      <button class="btn btn-danger" href="#" data-id="<?php echo $order->id; ?>"
                        data-nonce="<?php echo wp_create_nonce( 'update_order_status_'.$order->id); ?>"
                        data-toggle="modal" data-target="#confirm-delete" >ยกเลิกคำสั่งซื้อ</button>
                    <?php } ?>
                  </div>
                  <div class="order-col-4" style="text-align:center;min-height:1px;">
                    <button class="btn btn-primary" href="#" data-id="<?php echo $order->id; ?>"
                      id="flip"
                    >แสดงรูปภาพ</button>
                  </div>
                  <div class="order-col-4" style="text-align:right;min-height:1px;">
                    <?php if($order->status == 1){ ?>
                      <button class="btn btn-success" href="#" data-id="<?php echo $order->id; ?>"
                        data-nonce="<?php echo wp_create_nonce( 'add_transfer_slip_picture_'.$order->id); ?>"
                        data-toggle="modal" data-target="#add-transfer-slip"
                      >อัพโหลดรูปภาพ</button>
                    <?php } ?>
                  </div>
                </div>
                <div class="order-clear"></div>

                <div class="order-row" id="toggle_pic_<?php echo $order->id; ?>" style="display:none;text-align:center;">
                  <?php if($order->image_slip != ''){ ?>
                    <img id="slip_pic_<?php echo $order->id; ?>" src="<?php echo $uploads['baseurl'].$order->image_slip; ?>" />
                  <?php }else{ ?>
                    <img id="slip_pic_<?php echo $order->id; ?>" src="" style="display:none;" />
                  <?php } ?>
                </div>
                <div class="order-clear"></div>
             </div>
          <?php } ?>

          </div>




        <?php }//end foreach ($arrOrders as $order) ?>
        </section>
        <?php // end article section ?>
        <footer class="article-footer cf"> </footer>
      </article>

    </div>

  </div>
</div>
<?php get_footer(); ?>
