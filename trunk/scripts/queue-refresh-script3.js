    docReady(function(){
        table();
    })
    
    //load values when doc ready initialization function
    function docReady(fn) {
        // see if DOM is already available
        if (document.readyState === "complete" || document.readyState === "interactive") {
            // call on next available tick
            setTimeout(fn, 1);
        } else {
            document.addEventListener("DOMContentLoaded", fn);
        }
    }    
    
    function table(){
        var url = queuing.pluginsUrl;
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function(){
            document.getElementById("currentqueue").innerHTML = this.responseText;
        }
        xhttp.open("GET", url);
        xhttp.send();
    }

    setInterval(function(){
        table();
    }, 5000);