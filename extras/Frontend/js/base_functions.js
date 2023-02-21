function jsonEscape(str)  {
    return str.replace(/\n/g, "\\\\n").replace(/\r/g, "\\\\r").replace(/\t/g, "\\\\t").replace(/\"/g, "\\\"");
}

function javaEscape(str)  {
    return str.replace(/\'/g, "\\\\'");
}


function decodeHtml(html) {
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}

function epoch(date) {
    var msepoch=Date.parse(date)
    epoch=(msepoch/1000)
    return  epoch
}