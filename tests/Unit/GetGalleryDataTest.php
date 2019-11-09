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
        $postIDs = [];
        $responseCount = 5;

        // Create some gallery Posts
        while (count($postIDs) < $responseCount) {
            $request = $this->createGalleryPostRequest();
            $this->requestDataProviderParams($request);
            $this->requestDataProviderImage($request);

            $response = $this->dispatchRequest($request);
            $postResponse = $response->get_data();
            $postID = $postResponse['postID'];
            $postIDs[] = $postID;
        }

        // Retrieve Post data from route
        $getRequest = $this->createGalleryGetRequest();
        $getResponse = $this->dispatchRequest($getRequest);
        $responseData = $getResponse->get_data();

        // Show that post data retrieved matches that which was created
        $newIds = wp_list_pluck($responseData, 'ID');
        sort($newIds);
        sort($postIDs);
        $this->assertEqualSets($newIds, $postIDs);
    }

    /** @test */
    public function gallery_posts_can_be_paginated()
    {
        $postArray = [
            'post_content' => 'I am some words for the Content',
            'post_title'   => 'I am a default title',
            'post_type'    => 'gallery',
            'post_status'  => 'published'
        ];

        $postIds = $this->factory->post->create_many(31, $postArray);

        $pages = range(1, 4);
        $resultsPerPage = 10;

        $chunkedIDs = array_chunk($postIds, $resultsPerPage);

        $paginatedIDs = [];

        foreach ($pages as $page) {
            $getRequest  = $this->createGalleryGetRequest($page, $resultsPerPage, 'id', 'asc');
            $getResponse = $this->dispatchRequest($getRequest);
            $paginatedIDs[] = wp_list_pluck($getResponse->get_data(), 'ID');
        }

        $this->assertEqualSets($chunkedIDs, $paginatedIDs);
    }

    /** @test */
    public function gallery_pagination_can_be_ordered_both_asc_and_desc()
    {
        $postArray = [
            'post_content' => 'I am some words for the Content',
            'post_title'   => 'I am a default title',
            'post_type'    => 'gallery',
            'post_status'  => 'published'
        ];

        $start = time();
        $responses = [];

        for ($t = 0 ; $t < 31 ; ++$t) {
            $args = $postArray;
            $postDate = date('Y-m-d H:i:s', $start + ($t * 1000));
            $args['post_date'] = $postDate;

            $postId = $this->factory->post->create($args);

            $responses[] = [
                'ID' => $postId,
                'post_date' => $postDate,
            ];
        }

        $pages = range(1, 4);
        $resultsPerPage = 10;

        $paginatedResponsesAscending  = [];
        $paginatedResponsesDescending = [];

        function pluckIdAndPostDate(array $inputArray) {
            return [
                'ID' => $inputArray['ID'],
                'post_date' => $inputArray['post_date'],
            ];
        }

        foreach ($pages as $page) {
            $ascendingResponseData  = $this->sendGetRequest($page, $resultsPerPage, 'date', 'asc');
            $descendingResponseData = $this->sendGetRequest($page, $resultsPerPage, 'date', 'desc');

            $outAscending = [];
            $outDescending = [];

            foreach ($ascendingResponseData as $key => $ascendingResponseDatum) {
                $outAscending[]  = pluckIdAndPostDate($ascendingResponseDatum);
                $outDescending[] = pluckIdAndPostDate($descendingResponseData[$key]);
            }

            $paginatedResponsesAscending[] = $outAscending;
            $paginatedResponsesDescending[] = $outDescending;
        }

        $chunkedResponseAscending  = array_chunk($responses, $resultsPerPage);
        $chunkedResponseDescending = array_chunk(array_reverse($responses), $resultsPerPage);

        $this->assertEqualSets($chunkedResponseAscending, $paginatedResponsesAscending);
        $this->assertEqualSets($chunkedResponseDescending, $paginatedResponsesDescending);
    }
}