export const STATUSES = {
    1: 'Pending',
    2: 'Paid',
    3: 'Cancelled',
    4: 'Expired',
};

export const STATUS_COLOR = {
    1: 'yellow',
    2: 'green',
    3: 'red',
    4: 'gray',
};

export const getStatus = (status: number) => STATUSES[status as keyof typeof STATUSES];
export const getColor = (status: number) => STATUS_COLOR[status as keyof typeof STATUS_COLOR];

export const getRatingColor = (rating: number) => {
    if (rating >= 4) {
        return 'green';
    } else if (rating >= 3) {
        return 'yellow';
    } else {
        return 'red';
    }
}