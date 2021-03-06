<?php

require_once dirname(dirname(__FILE__)) . '/GalleryUnitTestCase.php';

class GetGalleryDataTest extends GalleryUnitTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /** @test */
    public function gallery_posts_can_be_retrieved()
    {
        $postIDs = $this->createPostsWithRequest(5);

        // Retrieve Post data from route
        $getRequest = $this->createRequestGetPaginatedGalleryItems();
        $getResponse = $this->dispatchRequest($getRequest);
        $responseData = $getResponse->get_data();
        $postData = $responseData['posts'];

        // Show that post data retrieved matches that which was created
        $newIds = wp_list_pluck($postData, 'ID');
        sort($newIds);
        sort($postIDs);
        $this->assertEqualSets($newIds, $postIDs);
    }

    /** @test */
    public function gallery_posts_can_be_paginated()
    {
        $postIds = $this->createGalleryPostWithFactory(31);

        $pages = range(1, 4);
        $resultsPerPage = 10;

        $chunkedIDs = array_chunk($postIds, $resultsPerPage);

        $paginatedIDs = [];

        foreach ($pages as $page) {
            $getRequest  = $this->createRequestGetPaginatedGalleryItems($page, $resultsPerPage, 'id', 'asc');
            $getResponse = $this->dispatchRequest($getRequest);
            $responseData = $getResponse->get_data();
            $postData = $responseData['posts'];
            $paginatedIDs[] = wp_list_pluck($postData, 'ID');
        }

        $this->assertEqualSets($chunkedIDs, $paginatedIDs);
    }

    /** @test */
    public function gallery_pagination_can_be_ordered_both_asc_and_desc()
    {
        $postIds = $this->createGalleryPostWithFactory(31, 1000);

        $pages = range(1, 4);
        $resultsPerPage = 10;

        $paginatedResponsesAscending  = [];
        $paginatedResponsesDescending = [];

        foreach ($pages as $page) {
            $ascendingResponseData  = $this->sendGetRequest($page, $resultsPerPage, 'date', 'asc');
            $ascendingPostData = $ascendingResponseData['posts'];
            $descendingResponseData = $this->sendGetRequest($page, $resultsPerPage, 'date', 'desc');
            $descendingPostData = $descendingResponseData['posts'];

            $outAscending = [];
            $outDescending = [];

            foreach ($ascendingPostData as $key => $ascendingResponseDatum) {
                $outAscending[]  = $ascendingResponseDatum['ID'];
                $outDescending[] = $descendingPostData[$key]['ID'];
            }

            $paginatedResponsesAscending[] = $outAscending;
            $paginatedResponsesDescending[] = $outDescending;
        }

        $chunkedPostIdsAscending  = array_chunk($postIds, $resultsPerPage);
        $chunkedPostIdsDescending = array_chunk(array_reverse($postIds), $resultsPerPage);

        $this->assertEqualSets($chunkedPostIdsAscending, $paginatedResponsesAscending);
        $this->assertEqualSets($chunkedPostIdsDescending, $paginatedResponsesDescending);
    }

    /** @test */
    public function individual_gallery_post_data_can_be_retrieved()
    {
        $data = $this->createGalleryPostWithMultipleImages();
        $createGalleryPostResponse = $data['response'];
        $postID = $createGalleryPostResponse->get_data();

        $newPost = get_post($postID);

        $getGalleryPostResponse = $this->createRequestGetSingleGalleryItem($postID);
        $getGalleryPostResponseData = $getGalleryPostResponse->get_data();

        $this->assertEquals(200, $getGalleryPostResponse->get_status());
        $this->assertEquals($newPost->ID, $getGalleryPostResponseData['ID']);
        $this->assertEquals($newPost->post_title, $getGalleryPostResponseData['post_title']);
        $this->assertEquals($newPost->post_content, $getGalleryPostResponseData['post_content']);

        $attachmentIds = get_post_meta($postID, $this->galleryAttachmentMetaKey, false);

        foreach ($attachmentIds as $key => $attachmentId) {
            $responseAttachId = $getGalleryPostResponseData['images'][$key]['attach_id'];

            $this->assertEquals($responseAttachId, $attachmentId);
        }
    }

    /** @test */
    public function gallery_posts_have_order_meta_data()
    {
        $count = 5;
        $postIDs = $this->createPostsWithRequest($count);

        $this->assertEquals($count, count($postIDs));

        foreach ($postIDs as $postID) {
            $meta = get_post_meta($postID, $this->galleryPostOrderKey);
            $this->assertFalse(empty($meta));
        }
    }

    /** @test */
    public function gallery_attachment_meta_data_has_order()
    {
        $data = $this->createGalleryPostWithMultipleImages();
        $createGalleryPostResponse = $data['response'];

        $postID = $createGalleryPostResponse->get_data();
        $attachmentIds = get_post_meta($postID, $this->galleryAttachmentMetaKey);

        $expectedOrder = 0;

        foreach ($attachmentIds as $attachmentId) {
            $attachmentOrder = get_post_meta($attachmentId, $this->galleryAttachmentOrderKey, true);
            $attachmentOrder = $attachmentOrder !== '' ? (int) $attachmentOrder : false;
            $this->assertEquals($expectedOrder, $attachmentOrder);
            ++$expectedOrder;
        }
    }

    /** @test */
    public function gallery_posts_can_be_organized_by_order()
    {
        $postIds = $this->createGalleryPostWithFactory(31, 1000);

        $lastPostId = $postIds[count($postIds)-1];

        $setGalleryOrder = 3;

        // Build the pagination ordered by gallery post order meta
        $expectedPaginatedGalleryPostIds = array_slice($postIds, 0, $setGalleryOrder);
        $expectedPaginatedGalleryPostIds[] = $lastPostId;
        $expectedPaginatedGalleryPostIds = array_merge($expectedPaginatedGalleryPostIds, array_slice($postIds, $setGalleryOrder));
        array_pop($expectedPaginatedGalleryPostIds);
        $expectedPaginatedGalleryPostIds = array_chunk($expectedPaginatedGalleryPostIds, 10);

        // Move the last gallery post to $setGalleryOrder
        $this->createGalleryUser(['administrator']);
        $response = $this->sendGalleryUpdateOrderRequest($lastPostId, $setGalleryOrder);

        $this->assertEquals(200, $response->get_status());

        $postMeta = get_post_meta($lastPostId, $this->galleryPostOrderKey, true);
        $this->assertEquals($postMeta, $setGalleryOrder);

        $pages = range(1, 4);
        $resultsPerPage = 10;

        $realPaginatedGalleryPostsIds = [];

        foreach ($pages as $page) {
            $paginatedIds = [];
            $pageResult = $this->sendGetRequest($page, $resultsPerPage, null, 'asc');
            $postData = $pageResult['posts'];
            foreach ($postData as $galleryPost) {
                $paginatedIds[] = $galleryPost['ID'];
            }
            $realPaginatedGalleryPostsIds[] = $paginatedIds;
        }

        $this->assertEquals($expectedPaginatedGalleryPostIds, $realPaginatedGalleryPostsIds);
    }

    /** @test */
    public function gallery_posts_data_includes_gallery_count()
    {
        $postIds = $this->createGalleryPostWithFactory(31, 1000);

        // Retrieve Post data from route
        $getRequest = $this->createRequestGetPaginatedGalleryItems();
        $getResponse = $this->dispatchRequest($getRequest);
        $responseData = $getResponse->get_data();

        $galleryCountData = (int) $responseData['gallery_count'];

        $this->assertEquals(count($postIds), $galleryCountData);
    }

    protected function createPostsWithRequest($count = false)
    {
        $postIDs = [];
        $count = $count === false ? 1 : (int) $count;

        while (count($postIDs) < $count) {
            $request = $this->createRequestSubmitGallery();
            $this->requestDataProviderParams($request);
            $this->requestDataProviderImage($request);

            $response = $this->dispatchRequest($request);
            $postIDs[] = $response->get_data();
        }

        return $postIDs;
    }
}