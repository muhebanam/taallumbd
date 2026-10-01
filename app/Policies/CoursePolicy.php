<?php
namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function update(User $user, Course $course): bool
    {
        return $user->isInstructor() && $course->instructor_id === $user->id;
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->update($user, $course);
    }

    public function accessContent(User $user, Course $course): bool
    {
        return $course->instructor_id === $user->id || $user->isEnrolled($course);
    }
}
