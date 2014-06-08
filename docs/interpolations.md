## Interpolations
With Stapler, uploaded files are accessed by configuring/defining path, url, and default_url strings which point to your uploaded file assets.  This is done via string interpolations.  Currently, the following interpolations are available for use:

*   **:attachment** - The name of the file attachment as declared in the hasAttachedFile function, e.g 'avatar'.
*   **:class**  - The classname of the model containing the file attachment, e.g User.  Stapler can handle namespacing of classes.
*   **:extension** - The file extension type of the uploaded file, e.g '.jpg'
*   **:filename** - The name of the uploaded file, e.g 'some_file.jpg'
*   **:id** - The id of the corresponding database record for the uploaded file.
*   **:id_partition** - The partitioned id of the corresponding database record for the uploaded file, e.g an id = 1 is interpolated as 000/000/001.  This is the default and recommended setting for Stapler.  Partioned id's help overcome the 32k subfolder problem that occurs in nix-based systems using the EXT3 file system.
*   **:hash** - An sha256 hash of the corresponding database record id.
*   **:app_root** - The path to the root of the project.
*   **:style** - The resizing style of the file (images only), e.g 'thumbnail' or 'original'.
*   **:url** - The url string pointing to your uploaded file.  This interpolation is actually an interpolation itself.  It can be composed of any of the above interpolations (except itself).