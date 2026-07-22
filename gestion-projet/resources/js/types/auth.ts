import type { User as IndexUser } from './index';

export type User = IndexUser;

export type Auth = {
    user: User | null;
};
