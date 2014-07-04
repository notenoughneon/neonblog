$("time").each(function() {
    var month = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
    var published = new Date($(this).attr("datetime"));
    var delta = $.now() - published.getTime();
    if (delta < 1000 * 60 * 60) {
        var minutes = Math.ceil(delta/(1000*60));
        $(this).text(minutes + "m");
    } else if (delta < 1000 * 60 * 60 * 24) {
        var hours = Math.ceil(delta/(1000*60*60));
        $(this).text(hours + "h");
    } else if (delta < 1000 * 60 * 60 * 24 * 30) {
        var days = Math.ceil(delta/(1000*60*60*24));
        $(this).text(days + "d");
    } else if (delta < 1000 * 60 * 60 * 24 * 365) {
        $(this).text(published.getDate() + " " + month[published.getMonth()]);
    } else {
        $(this).text(published.getDate() + " " + month[published.getMonth()] + " " + published.getFullYear());
    }
    return true;
});
