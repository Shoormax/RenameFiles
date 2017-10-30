function replaceSlashes(input)
{
    var textIn = $(input).val();
    if(textIn.search('\ ')) {
        $(input).val(textIn.replace('\\', '/'));
    }
}