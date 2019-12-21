<?php

namespace GmFrontendGallery;

trait definitionsTrait
{
    protected $galleryPostType = 'gallery';
    protected $galleryPostStatus = 'published';
    protected $galleryIncompleteCode = 'gallery_incomplete';
    protected $galleryAttachmentMetaKey = 'gm_gallery_attachment';
    protected $galleryPostOrderKey = 'gm_gallery_order';
    protected $galleryAttachmentOrderKey = 'gm_gallery_attachment_order';
    protected $routeNameSpace = 'gm-frontend-gallery/v1';
    protected $pluginOptionName = 'gm_frontend_gallery_options';
    protected $gallerySubmitNonce = 'gm_gallery_submit';

    protected $defaultOptions = [
        'user_required' => false,
        'admin_must_approve' => false,
        'max_attachments' => 5,
        'allowed_mimes' => [
            'image/jpeg',
            'image/gif',
            'image/png',
        ]
    ];
}