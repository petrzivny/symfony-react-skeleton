import { useQuery } from '@tanstack/react-query';
import axios from 'axios';

const BackendVersions = (): JSX.Element => {
  interface BeData {
    data: { phpVersion: string; symfonyVersion: string };
  }

  const queryFn = (): Promise<BeData> => axios.get<BeData>('/api/test-get-db-value').then((res) => res.data);

  const { isLoading, error, data, isFetching } = useQuery({
    queryKey: ['cacheIndex2'],
    queryFn: queryFn,
  });

  if (isLoading) {
    return <>Query is currently loading for the first time.</>;
  }

  if (isFetching) {
    return <>Query is fetching data.</>;
  }

  if (error) {
    return <>Error while fetching. See console for a description.</>;
  }

  return (
    <>
      <span>
        Symfony {data?.data.symfonyVersion} with Php {data?.data.phpVersion} (BE)
      </span>
    </>
  );
};

export default BackendVersions;
