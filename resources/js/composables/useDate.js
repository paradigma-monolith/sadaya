import dayjs from 'dayjs';
import 'dayjs/locale/id.js';
import relativeTime from 'dayjs/plugin/relativeTime.js';
import localizedFormat from 'dayjs/plugin/localizedFormat.js';

// Setup Indonesian locale and plugins
dayjs.extend(relativeTime);
dayjs.extend(localizedFormat);
dayjs.locale('id');

export function useDate() {
  /**
   * Format date into localized Indonesian string
   * Default: "25 Oktober 2026"
   */
  const formatDate = (date, format = 'DD MMMM YYYY') => {
    if (!date) return '-';
    return dayjs(date).format(format);
  };

  /**
   * Format date and time into localized Indonesian string
   * Default: "25 Oktober 2026, 11:05 WIB"
   */
  const formatDateTime = (date, format = 'DD MMMM YYYY, HH:mm [WIB]') => {
    if (!date) return '-';
    return dayjs(date).format(format);
  };

  /**
   * Relative time from now (e.g. "5 menit yang lalu", "baru saja")
   */
  const fromNow = (date) => {
    if (!date) return '-';
    return dayjs(date).fromNow();
  };

  return {
    dayjs,
    formatDate,
    formatDateTime,
    fromNow,
  };
}

export default useDate;
