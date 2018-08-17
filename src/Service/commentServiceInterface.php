<?php

namespace Blog\Service;

interface commentServiceInterface {

    public function createCommentForm($comment);
    
    public function createComment();
    
    public function setNewComment($comment, $currentUser, $blog = null, $parentComment = null);
    
    public function storeComment($comment);
    
    public function getCommentById($id);
    
    
}
