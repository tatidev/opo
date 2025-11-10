
  
  /*
  
    Dropdowns settings
    config of multipleselect dropdown

  */
  
  function init_dropdowns(given_target=null){
    var target = document;
    if( given_target !== null ) target = given_target;
    var set_template = {
      button: '<button type="button" class="multiselect dropdown-toggle" style="background: #e9ecef;'+ (true? 'width:100%' : 'max-width: 397.5px;') +';" data-toggle="dropdown"><span class="multiselect-selected-text"></span></button>',
      ul: '<ul class="multiselect-container dropdown-menu" style="width: 100%;"></ul>',
      filter: '<li class="multiselect-item filter"><div class="input-group">'+/*'<span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span>' + */'<input class="form-control multiselect-search" type="text"></div></li>',
      filterClearBtn: '<span class="hide input-group-btn"><button class="btn btn-default multiselect-clear-filter" type="button"><i class="glyphicon glyphicon-remove-circle"></i></button></span>',
      li: '<li><a href="javascript:void(0);"><label style="width:100%;"></label></a></li>',
      divider: '<li class="multiselect-item divider"></li>',
      liGroup: '<li class="multiselect-item group"><label class="multiselect-group"></label></li>'
    };

    $(target).find('.multi-dropdown').each(function(){
      var wFilter = ( $(this).hasClass('w-filtering') ? true : false );
      $(this).multiselect({
        buttonWidth: '100%',
        maxHeight: 300,
        enableFiltering: wFilter,
        enableCaseInsensitiveFiltering: wFilter,
        templates: set_template,
				buttonContainer: '<div class="btn-group" />', // '<div class="btn-group" style="height:100%" />',
        nonSelectedText: $(this).attr('placeholder'),
        onDropdownShown : function(event) {
          this.$select.parent().find("button.multiselect-clear-filter").click();
          this.$select.parent().find("input[type='text'].multiselect-search").focus();
        },
        onInitialized: function(select, container){
          $(select).hasClass('font-title') ? $('.multiselect-selected-text').addClass('font-title') : '';
        }
      });
      if( $(this).hasClass('disabled') ){
        $(this).multiselect('disable');
      }
    });

    $(target).find('.single-dropdown').each(function(){
      var wFilter = ( $(this).hasClass('w-filtering') ? true : false );
      $(this).multiselect({
        multiple: false,
        buttonWidth: '100%',
        maxHeight: 300,
        enableFiltering: wFilter,
        enableCaseInsensitiveFiltering: wFilter,
        templates: set_template,
				buttonContainer: '<div class="btn-group" />', // '<div class="btn-group" style="height:100%" />',
        nonSelectedText: $(this).attr('placeholder'),
        onDropdownShown : function(event) {
          this.$select.parent().find("button.multiselect-clear-filter").click();
          this.$select.parent().find("input[type='text'].multiselect-search").focus();
        },
        onInitialized: function(select, container){
          $(select).hasClass('font-title') ? $('.multiselect-selected-text').addClass('font-title') : '';
        }
      });
      if( $(this).hasClass('disabled') ){
        $(this).multiselect('disable');
      }
    });
    
  }
  
  init_dropdowns();

  function get_dropdown_selected_text(element, optionValue=null, optionIndex=null){
    var option;
    var _return = [];
    var elem = $(element);
    var elem_childrens = elem.children();
    var selected = elem.val();
    
    for( var i = 0; i < elem_childrens.length; i++ ){
      option = $(elem_childrens[i]);
      if( optionValue !== null ){
         if( option.val() === optionValue ){
          return option.html();
         }
      }
      else if( optionIndex !== null ){
        if( i === optionIndex ){
          return option.html();
        }
      }
      else if( selected === option.val() ) {
        return option.html();
      }
    }
    if( _return.length > 0 ){
      return _return.join(', ');
    }
    else {
      return undefined;
    }
  }