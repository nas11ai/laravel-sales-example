export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export interface RoleRow {
    id: number;
    name: string;
    permissions: string[];
    permissions_count: number;
}

export interface RoleData {
    id: number;
    name: string;
    permissions: string[];
}

export interface UserRow {
    id: number;
    name: string;
    email: string;
    role: string | null;
    created_at: string;
}

export interface UserData {
    id: number;
    name: string;
    email: string;
    role: string | null;
}
