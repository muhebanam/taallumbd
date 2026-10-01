import { Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

export default function ArticleShow({ article }) {
    return (
        <AppLayout>
            <Head title={article.title} />
            <article className="mx-auto max-w-3xl px-4 py-12">
                <span className="rounded-full bg-brand-light px-3 py-0.5 text-xs font-medium text-brand">{article.category?.name}</span>
                <h1 className="mt-4 text-3xl font-bold leading-snug text-brand-deep">{article.title}</h1>
                <p className="mt-2 text-sm text-brand-text/60">✍️ {article.author?.name} • {article.published_at ? new Date(article.published_at).toLocaleDateString('bn-BD') : ''}</p>
                <div className="prose mt-8 max-w-none whitespace-pre-line leading-relaxed text-brand-text/90">{article.body}</div>
            </article>
        </AppLayout>
    );
}
