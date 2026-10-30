export function money(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value || 0));
}

export function statusTone(status) {
    const map = {
        todo: 'bg-amber-50 text-amber-700 ring-amber-200',
        in_progress: 'bg-sky-50 text-sky-700 ring-sky-200',
        done: 'bg-violet-50 text-violet-700 ring-violet-200',
        completed: 'bg-brand-50 text-brand-700 ring-brand-200',
        pending: 'bg-soft-100 text-soft-600 ring-soft-200',
        partial: 'bg-orange-50 text-orange-700 ring-orange-200',
        paid: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    };

    return map[status] || 'bg-soft-100 text-soft-600 ring-soft-200';
}
