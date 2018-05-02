## Integration

### CKEditor 4

Add to CKEditor config

```js
CKEDITOR.replace('editor-id', {filebrowserImageBrowseUrl: '/file-manager/ckeditor'});
```
  
OR in to the config.js

```js
CKEDITOR.editorConfig = function( config ) {
  
  //...
  
  // Upload image
  config.filebrowserImageBrowseUrl = '/file-manager/ckeditor';
};
```
  
After these actions, you will be able to call the file manager from the CKEditor editor menu (Image -> Selection on the server).
The file manager will appear in a new window. If you want to somehow modify it, add some styles, change the height of the file manager, then you need to publish the view file responsible for displaying it -

```bash
php artisan vendor:publish --tag=fm-views
```
  
The file will now be located on the following path - `resources/views/vendor/file-manager/ckeditor.blade.php` - now it can be safely modified.