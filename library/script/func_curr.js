function formatCurrency(num) {

/* Strip commas before processing */
num = num.toString().replace(/\,/g,"");

var akhir = num.toString().charAt(num.toString().length-1);
/* Round to two decimal places if longer */
num = !isNaN(num) ? Math.round(num * 100) / 100 : 0;
if(akhir == '.') num = num.toString() + '.';
/* Add decimal and zeroes if none exist */
//num.toString().indexOf(".") == -1 ? num += ".00" : void 0;

/* Add zeroes if only decimal exists or only one digit after decimal */
//while(/\.\d{0,1}$/.test(num)) num += "0";

/* Add commas */
var objRegExp = new RegExp('(-?\[0-9]+)([0-9]{3})');
while(objRegExp.test(num)) num = num.toString().replace(objRegExp,'$1,$2');

/* Return formatted currency */
return num;
}

function stripCurrency(num) {
    return num.toString().replace(/\,/g,"");
}