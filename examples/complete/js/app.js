
dom.on_loaded(function () {
            
    var observer = null;
                    
    function observer_callback(changes, observer) { 
            
        for (change of changes) {
            
            if (change.isIntersecting)
            {   
                if (change.target.classList.contains("observed"))
                {
                    change.target.classList.remove("observed");
                    change.target.classList.add("seen");
                }
                              
                observer.unobserve(change.target);
            }
        };
    };

    function scan_page()
    {       
        document.querySelectorAll("main *").forEach(function (e,i) {
            e.classList.add("observed");
            observer.observe(e);    
        });
    }

    dom.on_scroll(function() {

        var options = { rootMargin: '-64px 200% 0px 200%' };
        observer = new IntersectionObserver(observer_callback, options);

        setTimeout(scan_page, 0);
        dom.on_ajax(scan_page);

        var seen = null;                
        document.querySelectorAll("main .seen").forEach(function (element) {
            if (seen == null && element.getBoundingClientRect().top > 0) { seen = element; } 
        });

        if (seen != null) {
            var y0 = seen.getBoundingClientRect().top;
        
            /* Moving */

            var y1 = seen.getBoundingClientRect().top;
            window.scroll(0, window.scrollY + y1 - y0);                
        }
        else {
            /* Moving */
        }

    });

});