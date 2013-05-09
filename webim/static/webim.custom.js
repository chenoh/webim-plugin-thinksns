/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function(){
    var isAlt = false;
    var isCtrl = false;
    $(this).keydown(function(e){
        if (e.which === 18)    
            isAlt = true;   
        if (e.which === 17)    
            isCtrl = true;   
        if (!isCtrl && isAlt && e.keyCode == '67' ) {
            webimUI.layout.removeChat(webimUI.layout.activeTabId);
            return false; 
        }
        if (isAlt && isCtrl && e.keyCode == '88' ) {
            focusToChatTab();
        }
        if (isAlt && isCtrl && e.keyCode == '67') {
            removeAllChat();
        }
    }).keyup(function(e){   
        if (e.which === 18)    
            isAlt = false;
        if (e.which === 17)    
            isCtrl = false;  
    });  
    
    function focusToChatTab () {
        var done = false, layout = webimUI.layout;
        webim.each(layout.tabs, function(key, tab) {
            if(!done && parseInt( tab.$.tabCount.innerHTML )){ 
                layout.focusChat(key);
                //tab.maximize();
                done = true;
            }
        });
    }
    function removeAllChat()
    {
        var layout = webimUI.layout;
        $.each(layout.tabIds, function(key,id){
            layout.removeChat(id);
        });
        
    }


});

