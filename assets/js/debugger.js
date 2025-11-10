
// Safe JSON stringify that handles circular references
function safeStringify(obj) {
  const seen = new WeakSet();
  return JSON.stringify(obj, function(key, value) {
    if (typeof value === "object" && value !== null) {
      if (seen.has(value)) {
        return '[Circular Reference]';
      }
      seen.add(value);
    }
    return value;
  });
}

window.addEventListener('error', function(e) {
  let s = '';
  try {
    s += safeStringify(e);
  } catch (err) {
    s += 'Error: ' + (e.message || 'Unknown error');
  }
  log_error(e);
});

jQuery(document).ajaxError(function(e, request, settings) {
  let s = '';
  try {
    s += safeStringify(e);
    s += safeStringify(request);
    s += safeStringify(settings);
  } catch (err) {
    s += 'AJAX Error (stringify failed)';
  }
  log_error(e);
});

function log_error(msg) {
  show_success_swal('Error ocurred: ' + msg, 'error');
  /*
  $.ajax({
    method: "POST",
    url: 'product/my_error_logging',
    dataType: 'json',
    data: {
      msg: msg
    },
    success: function(data, msg){
      show_success_swal('Error has been logged to the admin.', 'error');
    }
  });
  */
}