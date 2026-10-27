export type User = {
    id: number;
    username: string;
    full_name: string;
    email: string;
    initial_balance: number;
    last_login: string;
    created_at: string;
    updated_at: string;
};

export type Auth = {
    user: User;
};
