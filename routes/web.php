<?php

use App\Http\Controllers as C;
use Illuminate\Support\Facades\Route;

/* ---------------- Public ---------------- */
Route::get('/', C\HomeController::class)->name('home');

Route::get('/courses', [C\CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/category/{slug}', [C\CourseController::class, 'category'])->name('courses.category');
Route::get('/courses/{course}', [C\CourseController::class, 'show'])->name('courses.show');

Route::get('/articles', [C\ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/category/{slug}', [C\ArticleController::class, 'index'])->name('articles.category');
Route::get('/articles/{article}', [C\ArticleController::class, 'show'])->name('articles.show');

Route::get('/fatawa', [C\FatwaController::class, 'index'])->name('fatawa.index');
Route::get('/fatawa/ask', [C\FatwaController::class, 'ask'])->name('fatawa.ask');
Route::post('/fatawa/ask', [C\FatwaController::class, 'submit'])->name('fatawa.submit');
Route::get('/fatawa/category/{slug}', [C\FatwaController::class, 'index'])->name('fatawa.category');
Route::get('/fatawa/{fatwa}', [C\FatwaController::class, 'show'])->name('fatawa.show');

Route::get('/publications', [C\PublicationController::class, 'index'])->name('publications.index');
Route::get('/publications/{parent}/{child?}', [C\PublicationController::class, 'index'])->name('publications.category');

Route::get('/about/teachers', [C\PageController::class, 'teachers'])->name('about.teachers');
Route::get('/teachers/{teacher:slug}', [C\TeacherController::class, 'show'])->name('teachers.show');
Route::get('/about/{section?}', [C\PageController::class, 'about'])->name('about');
Route::get('/contact', [C\ContactController::class, 'show'])->name('contact');
Route::post('/contact', [C\ContactController::class, 'submit'])->name('contact.submit');
Route::get('/become-instructor', [C\BecomeInstructorController::class, 'index'])->name('become-instructor');
Route::get('/verify/{identifier}', [C\CertificateVerificationController::class, 'verify'])->name('certificates.verify');

/* ---------------- Quran & Hadith Library ---------------- */
Route::get('/quran', [C\QuranController::class, 'index'])->name('quran.index');
Route::get('/quran/{number}', [C\QuranController::class, 'show'])->name('quran.show');
Route::post('/quran/{number}/memorize', [C\QuranController::class, 'updateProgress'])->middleware('auth')->name('quran.memorize');

Route::get('/hadith', [C\HadithController::class, 'index'])->name('hadith.index');
Route::get('/hadith/{slug}', [C\HadithController::class, 'book'])->name('hadith.book');

/* ---------------- Islamic Community & Forum ---------------- */
Route::get('/community', [C\ForumController::class, 'index'])->name('community.index');
Route::get('/community/create', [C\ForumController::class, 'create'])->middleware('auth')->name('community.create');
Route::post('/community', [C\ForumController::class, 'store'])->middleware('auth')->name('community.store');
Route::get('/community/{post}', [C\ForumController::class, 'show'])->name('community.show');
Route::post('/community/{post}/comment', [C\ForumController::class, 'storeComment'])->middleware('auth')->name('community.comment');
Route::post('/community/{post}/like', [C\ForumController::class, 'toggleLike'])->middleware('auth')->name('community.like');
Route::post('/community/{post}/solved', [C\ForumController::class, 'markSolved'])->middleware('auth')->name('community.solved');

/* ---------------- Auth ---------------- */
Route::middleware('guest')->group(function () {
    Route::get('/register', [C\Auth\RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [C\Auth\RegisteredUserController::class, 'store']);
    Route::get('/login', [C\Auth\AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [C\Auth\AuthenticatedSessionController::class, 'store']);
});
Route::post('/logout', [C\Auth\AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

/* ---------------- Student (any authenticated user) ---------------- */
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [C\Student\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/my-courses', [C\Student\DashboardController::class, 'index'])->name('student.courses.index');
    Route::get('/dashboard/courses/{course}', [C\Student\CourseController::class, 'show'])->name('student.courses.show');
    Route::get('/dashboard/lessons/{lesson}', [C\Student\LessonController::class, 'show'])->name('student.lessons.show');
    Route::post('/dashboard/lessons/{lesson}/complete', [C\Student\LessonController::class, 'complete'])->name('student.lessons.complete');

    // Lesson Notes, Bookmarks, and Comments/Q&A
    Route::get('/dashboard/lessons/{lesson}/note', [C\Student\LessonInteractionController::class, 'getNote'])->name('student.lessons.note.get');
    Route::post('/dashboard/lessons/{lesson}/note', [C\Student\LessonInteractionController::class, 'saveNote'])->name('student.lessons.note.save');
    Route::post('/dashboard/lessons/{lesson}/bookmarks', [C\Student\LessonInteractionController::class, 'saveBookmark'])->name('student.lessons.bookmarks.store');
    Route::delete('/dashboard/bookmarks/{bookmark}', [C\Student\LessonInteractionController::class, 'deleteBookmark'])->name('student.lessons.bookmarks.destroy');
    Route::get('/dashboard/lessons/{lesson}/comments', [C\Student\LessonInteractionController::class, 'getComments'])->name('student.lessons.comments.index');
    Route::post('/dashboard/lessons/{lesson}/comments', [C\Student\LessonInteractionController::class, 'postComment'])->name('student.lessons.comments.store');

    Route::get('/dashboard/quizzes/{quiz}', [C\Student\QuizController::class, 'show'])->name('student.quizzes.show');
    Route::post('/dashboard/quizzes/{quiz}/submit', [C\Student\QuizController::class, 'submit'])->name('student.quizzes.submit');
    Route::get('/dashboard/assignments/{assignment}', [C\Student\AssignmentController::class, 'show'])->name('student.assignments.show');
    Route::post('/dashboard/assignments/{assignment}/submit', [C\Student\AssignmentController::class, 'submit'])->name('student.assignments.submit');
    Route::get('/dashboard/certificates', [C\Student\CertificateController::class, 'index'])->name('student.certificates');
    Route::post('/dashboard/certificates/{course}/generate', [C\Student\CertificateController::class, 'generate'])->name('certificates.generate');
    Route::get('/certificates/{certificate}', [C\Student\CertificateController::class, 'show'])->name('certificates.show');
    Route::get('/dashboard/orders', [C\Student\StudentOrderController::class, 'index'])->name('student.orders.index');
    Route::get('/dashboard/my-questions', [C\FatwaController::class, 'myQuestions'])->name('student.fatawa.mine');

    // Member article submission (goes to admin approval as 'pending')
    Route::get('/dashboard/articles/create', [C\Student\ArticleController::class, 'create'])->name('member.articles.create');
    Route::post('/dashboard/articles', [C\Student\ArticleController::class, 'store'])->name('member.articles.store');

    // Payment
    Route::get('/checkout/{course}', [C\CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{course}', [C\CheckoutController::class, 'process'])->name('checkout.process');
    Route::post('/checkout/{course}/coupon', [C\CheckoutController::class, 'checkCoupon'])->name('checkout.coupon');
    Route::get('/mock-payment/{order}', [C\CheckoutController::class, 'mockPayment'])->name('payment.mock');
    Route::post('/mock-payment/{order}/success', [C\CheckoutController::class, 'mockSuccess'])->name('payment.mock.success');
    Route::post('/payment/{order}/manual-submit', [C\CheckoutController::class, 'submitManualPayment'])->name('payment.manual.submit');
    Route::get('/orders/{order}/invoice', [C\CheckoutController::class, 'invoice'])->name('orders.invoice');
    Route::get('/invoice/{order}', [C\CheckoutController::class, 'invoice'])->name('invoice');

    // Teacher interaction routes
    Route::post('/teachers/{teacher}/follow', [C\TeacherFollowController::class, 'store'])->name('teachers.follow');
    Route::post('/teachers/{teacher}/unfollow', [C\TeacherFollowController::class, 'destroy'])->name('teachers.unfollow');
    Route::post('/teachers/{teacher}/questions', [C\TeacherQuestionController::class, 'store'])->name('teachers.questions.store');
    Route::post('/teachers/{teacher}/reviews', [C\TeacherReviewController::class, 'store'])->name('teachers.reviews.store');
});

/* ---------------- Instructor ---------------- */
Route::middleware(['auth', 'role:instructor,admin'])->prefix('instructor')->name('instructor.')->group(function () {
    Route::get('/dashboard', [C\Instructor\DashboardController::class, 'index'])->name('dashboard');
    Route::redirect('/course-builder', '/instructor/courses/create')->name('course-builder');
    Route::get('/courses/create', [C\Instructor\CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [C\Instructor\CourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{course}/edit', [C\Instructor\CourseController::class, 'edit'])->name('courses.edit');
    Route::put('/courses/{course}', [C\Instructor\CourseController::class, 'update'])->name('courses.update');
    Route::get('/courses/{course}/students', [C\Instructor\CourseController::class, 'students'])->name('courses.students');
    // Sections
    Route::post('/courses/{course}/sections', [C\Instructor\CurriculumController::class, 'storeSection'])->name('sections.store');
    Route::put('/courses/{course}/sections/{section}', [C\Instructor\CurriculumController::class, 'updateSection'])->name('sections.update');
    Route::delete('/courses/{course}/sections/{section}', [C\Instructor\CurriculumController::class, 'deleteSection'])->name('sections.destroy');

    // Lessons
    Route::post('/courses/{course}/sections/{section}/lessons', [C\Instructor\CurriculumController::class, 'storeLesson'])->name('lessons.store');
    Route::put('/courses/{course}/lessons/{lesson}', [C\Instructor\CurriculumController::class, 'updateLesson'])->name('lessons.update');
    Route::delete('/courses/{course}/lessons/{lesson}', [C\Instructor\CurriculumController::class, 'deleteLesson'])->name('lessons.destroy');

    // Quizzes
    Route::post('/courses/{course}/sections/{section}/quizzes', [C\Instructor\CurriculumController::class, 'storeQuiz'])->name('quizzes.store');
    Route::put('/courses/{course}/quizzes/{quiz}', [C\Instructor\CurriculumController::class, 'updateQuiz'])->name('quizzes.update');
    Route::delete('/courses/{course}/quizzes/{quiz}', [C\Instructor\CurriculumController::class, 'deleteQuiz'])->name('quizzes.destroy');

    // Assignments
    Route::post('/courses/{course}/sections/{section}/assignments', [C\Instructor\CurriculumController::class, 'storeAssignment'])->name('assignments.store');
    Route::put('/courses/{course}/assignments/{assignment}', [C\Instructor\CurriculumController::class, 'updateAssignment'])->name('assignments.update');
    Route::delete('/courses/{course}/assignments/{assignment}', [C\Instructor\CurriculumController::class, 'deleteAssignment'])->name('assignments.destroy');

    // Resources
    Route::post('/courses/{course}/sections/{section}/resources', [C\Instructor\CurriculumController::class, 'storeResource'])->name('resources.store');
    Route::put('/courses/{course}/resources/{resource}', [C\Instructor\CurriculumController::class, 'updateResource'])->name('resources.update');
    Route::delete('/courses/{course}/resources/{resource}', [C\Instructor\CurriculumController::class, 'deleteResource'])->name('resources.destroy');

    // Live Classes
    Route::post('/courses/{course}/sections/{section}/live-classes', [C\Instructor\CurriculumController::class, 'storeLiveClass'])->name('live-classes.store');
    Route::put('/courses/{course}/live-classes/{liveClass}', [C\Instructor\CurriculumController::class, 'updateLiveClass'])->name('live-classes.update');
    Route::delete('/courses/{course}/live-classes/{liveClass}', [C\Instructor\CurriculumController::class, 'deleteLiveClass'])->name('live-classes.destroy');

    // Reorder
    Route::post('/courses/{course}/curriculum/reorder', [C\Instructor\CurriculumController::class, 'reorder'])->name('curriculum.reorder');

    Route::post('/submissions/{submission}/review', [C\Instructor\CurriculumController::class, 'reviewSubmission'])->name('submissions.review');

    // Self profile management
    Route::get('/profile/teacher', [C\Instructor\InstructorTeacherProfileController::class, 'edit'])->name('profile.teacher.edit');
    Route::post('/profile/teacher', [C\Instructor\InstructorTeacherProfileController::class, 'store'])->name('profile.teacher.store');
    Route::put('/profile/teacher', [C\Instructor\InstructorTeacherProfileController::class, 'update'])->name('profile.teacher.update');
    Route::get('/teacher-questions', [C\Instructor\InstructorTeacherProfileController::class, 'questions'])->name('profile.teacher.questions');
    Route::post('/teacher-questions/{question}/answer', [C\Instructor\InstructorTeacherProfileController::class, 'answerQuestion'])->name('profile.teacher.questions.answer');
    Route::post('/teacher-questions/{question}/reject', [C\Instructor\InstructorTeacherProfileController::class, 'rejectQuestion'])->name('profile.teacher.questions.reject');
});

/* ---------------- Admin ---------------- */
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [C\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [C\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/students', [C\Admin\UserController::class, 'index'])->name('students.index');
    Route::put('/users/{user}/role', [C\Admin\UserController::class, 'updateRole'])->name('users.role');
    Route::delete('/users/{user}', [C\Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/courses', [C\Admin\ModerationController::class, 'courses'])->name('courses.index');
    Route::put('/courses/{course}/status', [C\Admin\ModerationController::class, 'updateCourseStatus'])->name('courses.status');
    Route::get('/articles', [C\Admin\ModerationController::class, 'articles'])->name('articles.index');
    Route::put('/articles/{article}/status', [C\Admin\ModerationController::class, 'updateArticleStatus'])->name('articles.status');
    Route::get('/enrollments', [C\Admin\ModerationController::class, 'enrollments'])->name('enrollments.index');
    Route::get('/orders', [C\Admin\ModerationController::class, 'orders'])->name('orders.index');
    Route::post('/orders/{order}/approve', [C\Admin\ModerationController::class, 'approveOrder'])->name('orders.approve');
    Route::post('/orders/{order}/reject', [C\Admin\ModerationController::class, 'rejectOrder'])->name('orders.reject');
    Route::get('/contact-messages', [C\Admin\ModerationController::class, 'contactMessages'])->name('messages.index');
    Route::put('/contact-messages/{message}/read', [C\Admin\ModerationController::class, 'markMessageRead'])->name('messages.read');

    // Teacher management
    Route::get('/teachers', [C\Admin\AdminTeacherController::class, 'index'])->name('teachers.index');
    Route::get('/teachers/create', [C\Admin\AdminTeacherController::class, 'create'])->name('teachers.create');
    Route::post('/teachers', [C\Admin\AdminTeacherController::class, 'store'])->name('teachers.store');
    Route::get('/teachers/{teacher}/edit', [C\Admin\AdminTeacherController::class, 'edit'])->name('teachers.edit');
    Route::put('/teachers/{teacher}', [C\Admin\AdminTeacherController::class, 'update'])->name('teachers.update');
    Route::delete('/teachers/{teacher}', [C\Admin\AdminTeacherController::class, 'destroy'])->name('teachers.destroy');
    Route::post('/teachers/{teacher}/verify', [C\Admin\AdminTeacherController::class, 'verify'])->name('teachers.verify');
    Route::post('/teachers/{teacher}/feature', [C\Admin\AdminTeacherController::class, 'feature'])->name('teachers.feature');
    Route::post('/teachers/{teacher}/activate', [C\Admin\AdminTeacherController::class, 'activate'])->name('teachers.activate');
    Route::post('/teachers/{teacher}/reject', [C\Admin\AdminTeacherController::class, 'reject'])->name('teachers.reject');

    // Teacher Questions & Reviews Moderation
    Route::get('/teacher-questions', [C\Admin\AdminTeacherQuestionController::class, 'index'])->name('teacher-questions.index');
    Route::post('/teacher-questions/{question}/answer', [C\Admin\AdminTeacherQuestionController::class, 'answer'])->name('teacher-questions.answer');
    Route::post('/teacher-questions/{question}/reject', [C\Admin\AdminTeacherQuestionController::class, 'reject'])->name('teacher-questions.reject');

    Route::get('/teacher-reviews', [C\Admin\AdminTeacherReviewController::class, 'index'])->name('teacher-reviews.index');
    Route::post('/teacher-reviews/{review}/approve', [C\Admin\AdminTeacherReviewController::class, 'approve'])->name('teacher-reviews.approve');
    Route::post('/teacher-reviews/{review}/reject', [C\Admin\AdminTeacherReviewController::class, 'reject'])->name('teacher-reviews.reject');

    // Instructor Applications
    Route::get('/instructor-applications', [C\Admin\InstructorApplicationController::class, 'index'])->name('instructor-applications.index');
    Route::put('/instructor-applications/{application}/approve', [C\Admin\InstructorApplicationController::class, 'approve'])->name('instructor-applications.approve');
    Route::put('/instructor-applications/{application}/reject', [C\Admin\InstructorApplicationController::class, 'reject'])->name('instructor-applications.reject');
});

/* Fatwa answering: admin OR instructor (scholar) */
Route::middleware(['auth', 'role:admin,instructor'])->group(function () {
    Route::get('/admin/fatawa', [C\Admin\ModerationController::class, 'fatawa'])->name('admin.fatawa.index');
    Route::put('/admin/fatawa/{fatwa}/answer', [C\Admin\ModerationController::class, 'answerFatwa'])->name('admin.fatawa.answer');
});
