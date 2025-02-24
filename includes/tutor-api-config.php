<?php

// this file meant to be only for tutor api things.

class TutorAPIConfig {
    const API_BASE_URL = 'https://wedti.com/wp-json';
    
    // Update with the provided credentials
    const API_KEY = 'key_1a3b36b770d1597540eeafd73dc36ba1';
    const API_SECRET = 'secret_54ec6ad12a9056f8b2281a071acc38009d38e25a1c904a12eeec11395f0c6f99';
    
    // Endpoints
    const ENDPOINT_COURSES = '/tutor/v1/courses';
    const ENDPOINT_TOPICS = '/tutor/v1/topics';
    const ENDPOINT_LESSONS = '/tutor/v1/lessons';
    const ENDPOINT_QUIZ = '/tutor/v1/quizzes';
    
} 

?>