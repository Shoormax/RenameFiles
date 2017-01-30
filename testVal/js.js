/**
 * Created by Shosho on 30/01/2017.
 */

function replaceSlashes(input)
{
    var textIn = $(input).val();
    if(textIn.search('\ ')) {
        $(input).val(textIn.replace('\\', '/'));
    }
}