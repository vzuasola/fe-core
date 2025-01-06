
function serializer(f) {



  var i, j, q;
  if (!f || f.nodeName !== "FORM") {
    return "asdas";
  }
  i = j = void 0;
  q = [];
  i = f.length - 1;


  while (i >= 0) {
    if (f.elements[i].name === "") {
      i = i - 1;
      continue;
    }
    switch (f.elements[i].nodeName) {
      case "INPUT":
        switch (f.elements[i].type) {
          case "text":
          case "hidden":
          case "password":
          case "button":
          case "reset":
          case "submit":
            q.push(f.elements[i].name + "=" + encodeURIComponent(f.elements[i].value));
            break;
          case "checkbox":
          case "radio":
            if (f.elements[i].checked) {
              q.push(f.elements[i].name + "=" + encodeURIComponent(f.elements[i].value));
            }
            break;
          case "file":
            break;
        }
        break;
      case "TEXTAREA":
        q.push(f.elements[i].name + "=" + encodeURIComponent(f.elements[i].value));
        break;
      case "SELECT":
        switch (f.elements[i].type) {
          case "select-one":
            q.push(f.elements[i].name + "=" + encodeURIComponent(f.elements[i].value));
            break;
          case "select-multiple":
            j = f.elements[i].options.length - 1;
            while (j >= 0) {
              if (f.elements[i].options[j].selected) {
                q.push(f.elements[i].name + "=" + encodeURIComponent(f.elements[i].options[j].value));
              }
              j = j - 1;
            }
        }
        break;
      case "BUTTON":
        switch (f.elements[i].type) {
          case "reset":
          case "submit":
          case "button":
            q.push(f.elements[i].name + "=" + encodeURIComponent(f.elements[i].value));
        }
    }
    i = i - 1;
  }
  return q.join("&");
};


export default serializer;
