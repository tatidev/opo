
function cart() {
  var btnPrint = "#btnPrintMemotags";
  this.frontend_cart = {
    amount_x: 0,
    amount_count: function(n){
      this.amount_x += n;
      this.update();
    },
    reset: function(){
      this.amount_x = 0;
      this.update();
    },
    update: function(){
      $(this.amount_selector).html(this.amount_x);
    },
    amount_selector: "span#cart_amount"
  };
  this.constant_item = 'it';
  this.constant_ringset = 'rs';
  //this.addUrl = window.location.host + "/pms/cart/add";
  this.setUrl = setCartUrl;
  //this.removeUrl = window.location.href + "cart/remove";
  this.printingUrl = printingCartUrl;
  
  this.basket_items = [];
  this.basket_ringsets = [];
  
  this.add_item = function(product_type, product_id, item_id, qty){
    var added_qty = qty;
    var n = null;
    $.each( this.basket_items, function(index, value){
      if( value.item_id === item_id && value.product_type === product_type ){
        qty += value.qty;
        n = index;
        return;
      }
    } );
    if ( n !== null  ) this.basket_items.splice(n, 1);
    var data = {
      basket_type: this.constant_item,
      product_type: product_type,
      product_id: product_id,
      item_id: item_id,
      qty: qty
    };
    
    this.frontend_cart.amount_count( added_qty );
    this.basket_items.push(data);
  };
  
  this.print = function(print_type=null){
    $(btnPrint).addClass('disabled');
    var this_cart = this;
    var url = this.setUrl;
    var obj1 = this.basket_items;
    var obj2 = this.basket_ringsets;
    $.ajax({
      method: "POST",
      url: url,
      dataType: 'json',
      data: {
        print_type: print_type,
        basket_items: obj1,
        basket_ringsets: obj2
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.log(errorThrown);
      },
      success: function(data, msg) {
        // Update view
        if( data.qty > 0){
          window.open(this_cart.printingUrl, "_blank");
          this_cart.empty_cart();
        }
      }
    });
  }
  
  /**/
  
  this.remove_item = function(product_type, item_id, qty=0){
    var n = 0;
    var this_cart = this;
    $.each( this.basket_items, function(index, value){
      if( value.item_id === item_id && value.product_type === product_type ){
        if( qty === 0 || this.basket_items[n].qty <= qty ){
          this_cart.basket_items.splice(n, 1);
        } else {
          this_cart.basket_items[n].qty -= qty;
        }
        return;
       }
      n++;
    });
  };
  
  this.remove_ringset = function(product_type, product_id, qty){
    
  };
  
  this.empty_cart = function(){
    this.basket_items = [];
    this.basket_ringsets = [];
    this.frontend_cart.reset();
    $(btnPrint).removeClass('disabled');
  }
}

var the_printing_cart = new cart();