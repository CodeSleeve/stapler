## Advanced-Usage
When working with attachments, there may come a point where you wish to do things outside of the normal workflow.  For example, suppose you wish to clear out an attachment (empty the attachment fields in the underlying table record and remove the uploaded file from storage) without having to destroy the record itself.  As mentioned above, you can always set the attachment attribute to STAPLER_NULL on the record before saving, however this only works if you save the record itself afterwards.  In situations where you wish to clear the uploaded file from storage without saving the record, you can use the attachment's destroy method:

```php
// Remove all of the attachment's uploaded files and empty the attacment attributes on the model:
$profilePicture->photo->destroy();

// For finer grained control, you can remove thumbnail files only (attachment attributes in the model will not be emptied).
$profilePicture->photo->destroy(['thumbnail']);
```

You may also reprocess uploaded images on an attachment by calling the reprocess() command (this is very useful for adding new styles to an existing attachment type where records have already been uploaded).

```php
// Programmatically reprocess an attachment's uploaded images:
$profilePicture->photo->reprocess();
```