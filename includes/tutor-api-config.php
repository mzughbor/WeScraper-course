<?php

// this file meant to be only for tutor api things.

class TutorAPIConfig {
    const API_BASE_URL = 'https://wedti.com/wp-json';
    
    // Update with the provided credentials
    const API_KEY = 'key_b51d74eb8c1825d630090a858fcd681f';
    const API_SECRET = 'secret_7bce73fdbf7d7fc3f7ea04194d8fa7df0cb7ed2e3806fc2bc26311532cd42dac';
    
    // Endpoints
    const ENDPOINT_COURSES = '/tutor/v1/courses';
    const ENDPOINT_TOPICS = '/tutor/v1/topics';
    const ENDPOINT_LESSONS = '/tutor/v1/lessons';
    const ENDPOINT_QUIZ = '/tutor/v1/quizzes';
    
} 

?>
