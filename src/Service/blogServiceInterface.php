<?php

namespace Blog\Service;

/*
 * Entities
 */

use Blog\Entity\Blog;

interface blogServiceInterface {

    /**
     *
     * Create an url from a blog object
     *
     * @param       blog  $blog The object to create Blog url from
     * @return      string
     *
     */
    public function createBlogUrl(Blog $blog = null);

    /**
     *
     * Create new Blog object
     *
     * @return      object
     *
     */
    public function createBlog();

    /**
     *
     * Get blog object based on id
     *
     * @param       id  $id The id to fetch the blog from the database
     * @return      object
     *
     */
    public function getBlogById($id);

    /**
     *
     * Get array of blogs
     *
     * @return      array
     *
     */
    public function getBlogs();

    /**
     *
     * Get array of blogs
     *
     * @param       page  $page The page offset
     * @return      array
     *
     */
    public function getOnlineBlogsForPaginator($page = 1);

    /**
     *
     * Get array of blogs
     *
     * @param       searchterm  $searchTerm to search for in database
     * @param       page  $page The page offset
     * @return      array
     *
     */
    public function getBlogsBySearchPhrase($searchTerm, $page = 1);

    /**
     *
     * Get array of blogs
     *
     * @param       id  $id of the category
     * @param       page  $page The page offset
     * @return      array
     *
     */
    public function getBlogsByCategoryIdForPaginator($id, $page = 1);

    /**
     *
     * Get array of blogs
     *
     * @param       start  $start of the blogs
     * @param       end  $end The blogs offset
     * @return      array
     *
     */
    public function getOnlineBlogsBasedOnStartAndOffSet($start = 0, $end = 3);

    /**
     *
     * Get array of blogs
     *
     * @return      array
     *
     */
    public function getArchivedBlogs();

    /**
     *
     * Create form of an object
     *
     * @param       blog $event $blog
     * @return      form
     *
     */
    public function createBlogForm($blog);

    /**
     *
     * Set data to new blog
     *
     * @param       blog $blog object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function setNewBlog($blog, $currentUser);

    /**
     *
     * Set data to existing blog
     *
     * @param       blog $blog object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function setExistingBlog($blog, $currentUser);

    /**
     *
     * Archive blog
     *
     * @param       blog $blog object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function archiveBlog($blog, $currentUser);

    /**
     *
     * UnArchive blog
     *
     * @param       blog $blog object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function unArchiveBlog($blog, $currentUser);

    /**
     *
     * Set blog online or offline
     *
     * @param       blog $blog object
     * @param       status $status integer
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function setBlogOnlineOffline($blog, $status, $currentUser);

    /**
     *
     * Set youtube video to blog
     *
     * @param       blog $blog object
     * @param       youtube $youtube object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function addYouTubeToBlog($blog, $youtube, $currentUser);

    /**
     *
     * Save blog to database
     *
     * @param       blog object
     * @return      void
     *
     */
    public function storeBlog($blog);

    /**
     *
     * Delete a Blog object from database
     * @param       blog $blog object
     * @return      object
     *
     */
    public function deleteBlog($blog);
}
