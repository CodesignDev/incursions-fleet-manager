using System.Text;

namespace EsiProxy
{
    public static class StringExtensions
    {
        private static readonly Encoding _defaultEncoding = Encoding.UTF8;
        public static string ToBase64String(this string s)
        {
            var bytes = _defaultEncoding.GetBytes(s);
            return Convert.ToBase64String(bytes);
        }

        public static string FromBase64String(this string s)
        {
            var bytes = Convert.FromBase64String(s);
            return _defaultEncoding.GetString(bytes);
        }
    }
}
