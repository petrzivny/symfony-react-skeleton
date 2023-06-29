import { useQuery } from '@tanstack/react-query';
import axios from 'axios';

const BackendValue = (): JSX.Element => {
  interface BeData {
    data: { optionName: string; isSupported: string };
  }

  const queryFn = (): Promise<BeData> =>
    axios.get<BeData>('/api/test-get-db-value').then((res) => res.data);

  const { isLoading, error, data, isFetching } = useQuery({
    queryKey: ['cacheIndex1'],
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
      <p>Data from Backend database received.</p>
      Data: <span className={'db-data'}>{data?.data.optionName}: {data?.data.isSupported === "YES" ? 'Is supported' : 'Is not supported'}</span>
    </>
  );
};

export default BackendValue;
