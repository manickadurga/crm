import { useLocation } from 'react-router-dom';

const useBasePath = () => {
  const location = useLocation();
  const basePath = location.pathname.split('/')[1]; // Adjust based on your URL structure
  console.log('Base Path:', basePath); // Console log to debug
  return basePath;
};

export default useBasePath;
