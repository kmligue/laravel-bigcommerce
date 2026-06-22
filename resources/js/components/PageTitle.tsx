interface PageTitleProps {
  title: string;
}

export default function PageTitle({ title }: PageTitleProps) {
  return <h1 className="text-2xl font-semibold text-gray-800 mt-6">{title}</h1>;
}
