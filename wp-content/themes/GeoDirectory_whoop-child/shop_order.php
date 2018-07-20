<?php /* Template Name: Shop order */ ?>
<?php

global $wpdb, $current_user;

$pid = $_GET['pid'];
$is_current_user_owner = false;
if (isset($pid) && $pid != '') {
  $is_current_user_owner = geodir_listing_belong_to_current_user((int)$pid);
}
if (!is_user_logged_in() || !$is_current_user_owner)
  wp_redirect(home_url());

$uploads = wp_upload_dir();

get_header(); ?>

<script>
jQuery(document).ready(function($){

  jQuery(document).on("change", ".order-status", function(){
    var order_status = $(this).val();
    var order_id = $(this).data('id');
    var nonce = $(this).data('nonce');
    console.log(order_status+"--"+order_id+"--"+nonce);


    $( "#panel_"+order_id ).find('.wrapper-loading').toggleClass('order-status-loading');
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
              $( "#panel_"+order_id ).find('.wrapper-loading').toggleClass('order-status-loading');
            });


            //$('.wrapper-loading').toggleClass('cart-loading');

      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
         console.log(textStatus);
         $( "#panel_"+order_id ).find('.wrapper-loading').toggleClass('order-status-loading');
      }
    });

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

          <?php

          $arrOrders  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM orders where post_id =%d ORDER BY id DESC ",
                array($pid)
            )
          );

          foreach ($arrOrders as $order) {
            set_query_var( 'order_status', $order->status );
		      ?>

          <div class="panel <?php echo ($order->status == 99 ? 'panel-danger' : 'panel-default'); ?>" id="panel_<?php echo $order->id; ?>" >
            <div class="panel-heading">Order id: #<?php echo $order->id; ?> ร้าน: <a href="<?php echo get_page_link($order->post_id); ?>"><?php echo get_the_title($order->post_id); ?></a></div>
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
                  <div class="order-col-4" style="text-align:left;margin-top:10px;margin-bottom:10px;">
                    <select name="status" class="order-status" data-id="<?php echo $order->id; ?>" data-nonce="<?php echo wp_create_nonce( 'update_order_status_'.$order->id); ?>">
                        <option <?php if ($order->status == '1') echo ' selected="selected" '; ?> value="1">รอการจ่ายเงิน</option>
                        <option <?php if ($order->status == '2') echo ' selected="selected" '; ?> value="2">ยืนยันการจ่ายเงิน</option>
                        <option <?php if ($order->status == '3') echo ' selected="selected" '; ?> value="3">ทำการจัดส่งแล้ว</option>
                    </select>
                  </div>
                  <div class="order-col-4" style="text-align:center;min-height:1px;">
                    <button class="btn btn-primary" href="#" data-id="<?php echo $order->id; ?>"
                      id="flip"
                    >แสดงรูปภาพ</button>
                  </div>
                  <div class="order-col-4" style="text-align:right;min-height:1px;">

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
