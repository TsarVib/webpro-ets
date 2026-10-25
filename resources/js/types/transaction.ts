export type TransactionRow = {
    id: number;
    date: string;
    time: string | null;
    category: string | null;
    category_id: number;
    notes: string | null;
    amount: number;
};

export type TransactionFilters = {
    from?: string;
    to?: string;
    category?: number;
    q?: string;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type TransactionPaginator = {
    data: TransactionRow[];
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
    current_page: number;
    last_page: number;
};

export type CategoryOption = {
    id: number;
    name: string;
};
