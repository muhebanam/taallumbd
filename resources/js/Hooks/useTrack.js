import { useEffect, useRef, useCallback } from 'react';
import axios from 'axios';

// Persistent Session ID generator for client-side analytics
function getAnalyticsSessionId() {
    if (typeof window === 'undefined') return 'ssr_session';
    try {
        let sid = sessionStorage.getItem('taallum_analytics_sid');
        if (!sid) {
            sid = 'sid_' + Math.random().toString(36).substring(2, 12) + Date.now().toString(36);
            sessionStorage.setItem('taallum_analytics_sid', sid);
        }
        return sid;
    } catch (e) {
        return 'anon_session';
    }
}

// Global buffer for batched events
let eventQueue = [];
let flushTimeout = null;

function flushQueue() {
    if (eventQueue.length === 0) return;

    const batch = [...eventQueue];
    eventQueue = [];

    if (flushTimeout) {
        clearTimeout(flushTimeout);
        flushTimeout = null;
    }

    const payload = JSON.stringify({ events: batch });

    // Try navigator.sendBeacon if supported and unloading, otherwise axios
    if (typeof navigator !== 'undefined' && navigator.sendBeacon) {
        const blob = new Blob([payload], { type: 'application/json' });
        const sent = navigator.sendBeacon('/events', blob);
        if (sent) return;
    }

    axios.post('/events', { events: batch }, {
        headers: { 'Content-Type': 'application/json' }
    }).catch(err => {
        console.debug('Failed to flush analytics queue:', err?.message);
    });
}

function scheduleFlush() {
    if (flushTimeout) return;
    flushTimeout = setTimeout(() => {
        flushQueue();
    }, 1000);
}

// Ensure flush on window unload
if (typeof window !== 'undefined') {
    window.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            flushQueue();
        }
    });
    window.addEventListener('beforeunload', () => {
        flushQueue();
    });
}

/**
 * Core event dispatcher
 */
export function trackEvent(eventType, options = {}, immediate = false) {
    const sessionId = getAnalyticsSessionId();
    const event = {
        event_type: eventType,
        session_id: sessionId,
        course_id: options.course_id || null,
        subject_type: options.subject_type || null,
        subject_id: options.subject_id || null,
        properties: options.properties || {},
        occurred_at: options.occurred_at || new Date().toISOString().slice(0, 19).replace('T', ' '),
    };

    if (immediate) {
        axios.post('/events', event).catch(e => console.debug('Track error:', e?.message));
        return;
    }

    eventQueue.push(event);
    if (eventQueue.length >= 10) {
        flushQueue();
    } else {
        scheduleFlush();
    }
}

/**
 * React Hook: useTrack()
 */
export function useTrack() {
    const track = useCallback((eventType, options = {}, immediate = false) => {
        trackEvent(eventType, options, immediate);
    }, []);

    const trackLessonStarted = useCallback((lessonId, courseId, extra = {}) => {
        track('lesson_started', {
            subject_type: 'lesson',
            subject_id: lessonId,
            course_id: courseId,
            properties: extra,
        });
    }, [track]);

    const trackLessonCompleted = useCallback((lessonId, courseId, extra = {}) => {
        track('lesson_completed', {
            subject_type: 'lesson',
            subject_id: lessonId,
            course_id: courseId,
            properties: extra,
        }, true);
    }, [track]);

    const trackVideoProgress = useCallback((lessonId, courseId, percent, durationSeconds = 0, extra = {}) => {
        track('video_progress', {
            subject_type: 'lesson',
            subject_id: lessonId,
            course_id: courseId,
            properties: {
                percent,
                duration_seconds: durationSeconds,
                ...extra,
            },
        });
    }, [track]);

    const trackQuizAttempted = useCallback((quizId, courseId, extra = {}) => {
        track('quiz_attempted', {
            subject_type: 'quiz',
            subject_id: quizId,
            course_id: courseId,
            properties: extra,
        });
    }, [track]);

    const trackQuizPassed = useCallback((quizId, courseId, score, extra = {}) => {
        track('quiz_passed', {
            subject_type: 'quiz',
            subject_id: quizId,
            course_id: courseId,
            properties: {
                score,
                ...extra,
            },
        }, true);
    }, [track]);

    const trackAssignmentSubmitted = useCallback((assignmentId, courseId, extra = {}) => {
        track('assignment_submitted', {
            subject_type: 'assignment',
            subject_id: assignmentId,
            course_id: courseId,
            properties: extra,
        }, true);
    }, [track]);

    const trackCourseEnrolled = useCallback((courseId, extra = {}) => {
        track('course_enrolled', {
            subject_type: 'course',
            subject_id: courseId,
            course_id: courseId,
            properties: extra,
        }, true);
    }, [track]);

    const trackCourseCompleted = useCallback((courseId, extra = {}) => {
        track('course_completed', {
            subject_type: 'course',
            subject_id: courseId,
            course_id: courseId,
            properties: extra,
        }, true);
    }, [track]);

    const trackQuranRead = useCallback((surah, ayah = null, durationSeconds = 0, extra = {}) => {
        track('quran_read', {
            subject_type: 'quran',
            subject_id: surah,
            properties: {
                surah,
                ayah,
                duration_seconds: durationSeconds,
                ...extra,
            },
        });
    }, [track]);

    const trackHadithViewed = useCallback((book, hadithNumber, extra = {}) => {
        track('hadith_viewed', {
            subject_type: 'hadith',
            subject_id: hadithNumber,
            properties: {
                book,
                hadith_number: hadithNumber,
                ...extra,
            },
        });
    }, [track]);

    const trackFatwaViewed = useCallback((fatwaId, category = null, extra = {}) => {
        track('fatwa_viewed', {
            subject_type: 'fatwa',
            subject_id: fatwaId,
            properties: {
                category,
                ...extra,
            },
        });
    }, [track]);

    const trackSearch = useCallback((query, resultsCount = 0, extra = {}) => {
        track('search_performed', {
            subject_type: 'search',
            properties: {
                query,
                results_count: resultsCount,
                ...extra,
            },
        });
    }, [track]);

    const trackCheckoutStarted = useCallback((courseId, amount, extra = {}) => {
        track('checkout_started', {
            subject_type: 'course',
            subject_id: courseId,
            course_id: courseId,
            properties: {
                amount,
                ...extra,
            },
        });
    }, [track]);

    const trackCheckoutCompleted = useCallback((orderId, courseId, amount, gateway = null, extra = {}) => {
        track('checkout_completed', {
            subject_type: 'order',
            subject_id: orderId,
            course_id: courseId,
            properties: {
                order_id: orderId,
                amount,
                gateway,
                ...extra,
            },
        }, true);
    }, [track]);

    return {
        track,
        flushQueue,
        trackLessonStarted,
        trackLessonCompleted,
        trackVideoProgress,
        trackQuizAttempted,
        trackQuizPassed,
        trackAssignmentSubmitted,
        trackCourseEnrolled,
        trackCourseCompleted,
        trackQuranRead,
        trackHadithViewed,
        trackFatwaViewed,
        trackSearch,
        trackCheckoutStarted,
        trackCheckoutCompleted,
    };
}

export default useTrack;
