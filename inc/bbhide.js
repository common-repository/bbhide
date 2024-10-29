( function() {
    tinymce.PluginManager.add( 'bbhide_button', function( editor, url ) {

        // Add a button that opens a window
        editor.addButton( 'bbhide_button_button_key', {
			title : bbhidebutton.hide,
            text: false,
			image : url + '/hide.png',
            
            onclick: function() {
                // Open window
                editor.windowManager.open( {
                    title: 'BBHide',
                    body: [
					
					{type: 'textbox', name: 'text', label: bbhidebutton.text, 'multiline': 'true', 'minWidth': 380, 'minHeight': 140},
                    {type: 'textbox', name: 'comments', label: bbhidebutton.comments, 'maxWidth': 40},
                    {type: 'textbox', name: 'days', label: bbhidebutton.days, 'maxWidth': 40},
                   {type: 'listbox', name: 'style', label: bbhidebutton.style, 
                        'values': [
                            {text: bbhidebutton.green, value: 'green'},
                            {text: bbhidebutton.blue, value: 'blue'},
                            {text: bbhidebutton.yellow, value: 'yellow'},
                            {text: bbhidebutton.orange, value: 'orange'},
                            {text: bbhidebutton.tan, value: 'tan'},
                            {text: bbhidebutton.grey, value: 'grey'},
                            {text: bbhidebutton.red, value: 'red'}
                        ]
                    },
                   
					],
                    onsubmit: function( e ) {
                        // Insert content when the window form is submitted
                        editor.insertContent('[hide comments=\'' + e.data.comments + '\'' + ' days=\'' + e.data.days + '\' style=\'' + e.data.style + '\']' + e.data.text + '[/hide]');
                    }

                } );
            }

        } );

    } );

} )();