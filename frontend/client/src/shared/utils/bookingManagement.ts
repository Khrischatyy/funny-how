export const STATUSES = {
    1: 'Pending',
    2: 'Paid',
    3: 'Cancelled',
    4: 'Expired',
};

export const STATUS_COLOR = {
    1: 'text-yellow',
    2: 'text-green',
    3: 'text-red',
    4: 'text-gray',
};

export const getStatus = (status: number) => STATUSES[status as keyof typeof STATUSES];
export const getColor = (status: number) => STATUS_COLOR[status as keyof typeof STATUS_COLOR];

export const getRatingColor = (rating: number) => {
    if (rating >= 4) {
        return 'text-green';
    } else if (rating >= 3) {
        return 'text-yellow';
    } else {
        return 'text-red';
    }
}