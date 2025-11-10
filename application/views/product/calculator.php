<style>
  .calc_wrap > div > span {
    font-size: 30px;
  }
  
  .rpnBye {
    display: inline-block;
    width: 211px;
    padding-top: 10px;
  }

  .rpnBye>span {
    width: 100%;
  }

  .rpnBye>input {
    border: 1px solid #D9D9D9;
    border-top: 1px solid silver;
    font-size: 180%;
    height: 40px;
    padding: 1px 6px;
    text-align: center;
    width: 100%;
    border-radius: 2px 2px 0 0;
    border-radius: 2px 2px 0 0;
  }

  .rpnBye>select {
    background-position-x: 95%;
    width: 211px;
    border-radius: 0 0 2px 2px;
    border-radius: 0 0 2px 2px;
    margin-top: -1px;
    background: url(//ssl.gstatic.com/ui/v1/disclosure/grey-disclosure-arrow-up-down.png) 100% no-repeat whiteSmoke;
    border: 1px solid gainsboro;
    font-size: 13px;
    line-height: 20px;
    padding: 5px 16px 5px 5px;
    vertical-align: middle;
    -webkit-appearance: button;
    appearance: button;
  }
  
  .calc_error {
    background: #ff000026;
  }
  .calc_success {
    background: greenyellow;
  }
</style>

<div class='calc_wrap row'>
  
  <div class='col-xs-12 col-sm-4'>
    <p class=''>
      <u><b>How to use Calculator</b></u>
    </p>
    <p>
      1) First change UNITS accordingly with lengths you have and want.<br>
      2) Change Fabric Width field.<br>
      3) Fill either the Linear Length or Area.<br>
      4) Press enter and the blank field will be filled out.<br>
      5) Press Restart for new calculation.
    </p>
    <a id='btnResetCalc' href='https://www.google.com/search?q=unit+convertor' class='' target="_blank">
      <i class="fas fa-external-link"></i> Google Unit Convertor
    </a>
  </div>
  
  <div class='col-xs-12 col-sm-8'>
    <div class='rpnBye'>
      <span>Linear Length</span>
      <input type='number' name='calc_length' id='calc_length'>
      <select id='calc_length_select'>
        <option value="yd">yards</option>
        <option value="ft">foot</option>
        <option value="mt">meters</option>
      </select>
    </div>
    <span style='vertical-align: text-bottom;'><i class="fas fa-times"></i></span>
    <div class='rpnBye'>
      <span>Fabric Width</span>
      <input type='number' name='calc_width' id='calc_width'>
      <select id='calc_width_select'>
        <option value="in">inches</option>
        <option value="cm">cms</option>
      </select>
    </div>
    <span style='vertical-align: text-bottom;'><i class="fas fa-equals"></i></span>
    <div class='rpnBye'>
      <span>Area</span>
      <input type='number' name='calc_total' id='calc_total'>
      <select id='calc_total_select'>
        <option value="yd2">Square yard</option>
        <option value="ft2">Square foot</option>
        <option value="mt2">Square meter</option>
      </select>
    </div>
    <div class='rpnBye'>
      <a id='btnResetCalc' href='#' class='btn btn-lg'>
        <i class="fas fa-undo"></i> Reset
      </a>
    </div>
  </div>

</div>
<script>
  function get_factors_matrix() {
//     var factors = {
    return {
      'in': {
        'yd': 1/36,
        'ft': 1/12,
        'mt': 1/39.37
      },
      'cm': {
        'yd': 1/91.44,
        'ft': 1/30.48,
        'mt': 1/100
      },
      'yd': {
        'yd': 1,
        'ft': 3,
        'mt': 1/1.094
      },
      'ft': {
        'yd': 1/3,
        'ft': 1,
        'mt': 1/3.281
      },
      'mt': {
        'yd': 1.094,
        'ft': 3.281,
        'mt': 1
      },
      'yd2': {
        'yd2': 1,
        'ft2': 9,
        'mt2': 1/1.196
      },
      'ft2': {
        'yd2': 1/9,
        'ft2': 1,
        'mt2': 1/10.764
      },
      'mt2': {
        'yd2': 1.196,
        'ft2': 10.764,
        'mt2': 1
      }
    };
//     return factors;
  }
  
  function calc() {

    this._w, this._l, this._total = 0;
    this._factor = get_factors_matrix();

    this._error = false;
    this._target_errors = [];
    this._class = {
      error: 'calc_error',
      success: 'calc_success'
    };

    this._update = function(handler_id) {
      this._elem_last_change = $('#' + handler_id);
      this._w = $("input#calc_width").val();

      if( handler_id.includes("calc_length") || handler_id.includes("calc_total") ) {
        this._l = ( handler_id.includes("calc_length") ? $("#calc_length").val() : '' );
        this._total = ( handler_id.includes("calc_total") ? $("#calc_total").val() : '' );
      }
      else {
        this._l = ( $("#calc_length").val().length > 0 ? $("#calc_length").val() : '' );
        this._total = ( $("#calc_total").val().length > 0 ? $("#calc_total").val() : '' );
      }
      
      console.log(this);
      this._check_error();

      if (!this._error) {
        this._calculate();
      }
    }

    this.reset = function() {
      this._error = false;
      this._target_errors = [];
      $("#calc_width, #calc_length, #calc_total").removeClass(this._class.error +" "+ this._class.success);
    }

    this._check_error = function() {
      this.reset();
      if (this._w.length === 0) {
        this._target_errors.push("#calc_width");
      } else if (this._l.length === 0 && this._total.length === 0) {
        this._target_errors.push("#calc_length", "#calc_total");
      }

      if (this._target_errors.length > 0) {
        this._display_error();
        this._error = true;
      }
    }

    this._display_error = function() {
      for (let n = 0; n < this._target_errors.length; n++) {
        target = this._target_errors[n];
        $(target).addClass(this._class.error);
      }
    }

    this._convert = function(num, from, to) {
      return parseInt(num) * this._factor[from][to];
    };

    this._calculate = function() {
      this.target = {
        unit: null,
        elem: null,
        val: null
      };

      if (this._l.length === 0) {
        this.target.unit = $("#calc_length_select").val();
        this.target.elem = $("#calc_length");
        let t = this._convert(this._total, $("#calc_total_select").val(), this.target.unit+'2');
        let w = this._convert(this._w, $("#calc_width_select").val(), this.target.unit);
        this.target.val = t / w;
      } else if (this._total.length === 0) {
        this.target.unit = $("#calc_total_select").val();
        this.target.elem = $("#calc_total");
        let l = this._convert(this._l, $("#calc_length_select").val(), this.target.unit.replace('2', '') );
        let w = this._convert(this._w, $("#calc_width_select").val(), this.target.unit.replace('2', '') );
        this.target.val = l * w;
      }
//       console.log(this.target);
      this.target.elem.val( this.target.val.toFixed(2) );
      this.target.elem.addClass( this._class.success );
    }
  }

  var calc = new calc();

  $(document).ready(function() {

    $(document).on('change', '.rpnBye > input, .rpnBye > select', function(e) {
      //       console.log( $(e.target), $(e.target).prop('id') );
      calc._update($(e.target).prop('id'));
    })

    $(document).on('click', 'a#btnResetCalc', function() {
      var els = $('.rpnBye > input');
      for (var i = 0; i < els.length; i++) {
        $(els[i]).val('');
      }
      calc.reset();
    })

  })
</script>